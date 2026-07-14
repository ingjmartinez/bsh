<?php

namespace App\Services;

use App\Models\Agencia;
use App\Models\ChatbotSession;
use App\Models\ServicioGeneralRequerimiento;
use App\Models\TicketSolicitud;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WhatsAppChatbotService
{
    private const STEP_INICIO = 'inicio';

    private const STEP_SISTEMA = 'seleccion_sistema';

    private const STEP_MENU = 'consulta_hora_menu';

    private const STEP_TICKET_NUMERO = 'ticket_numero';

    private const STEP_TICKET_IMAGEN = 'ticket_imagen';

    private const STEP_TICKET_BLOQUEADO = 'ticket_bloqueado';

    private const STEP_SG_TIPO = 'servicios_generales_tipo';

    private const STEP_SG_TERMINAL = 'servicios_generales_terminal';

    private const STEP_SG_IMAGEN = 'servicios_generales_imagen';

    private const STEP_CONFIRMAR_CIERRE_SESION = 'confirmar_cierre_sesion';

    private const SISTEMA_MESSAGE = "Hola. Selecciona el sistema escribiendo solo el numero:\n\n1- Real\n2- Delta\n3- Lotedom";

    private const MENU_MESSAGE = "Hola. Soy el asistente virtual de BSH, comprometido contigo siempre.\n\nPara continuar, escribe solo el numero de la opcion que necesitas:\n\n1-Consultar horario de servicio\n2-Consultar servicios disponibles\n3-Pagar ticket\n4-Anular ticket\n5-Recursos Humanos\n6-Reportar averia\n\nEstoy listo para ayudarte.";

    private const CONFIRM_CLOSE_SESSION_MESSAGE = "Ya tienes una sesion abierta.\n\nQuieres cerrar la sesion actual o retomar donde te quedaste?\n\n1- Cerrar sesion\n2- Retomar";

    private const INVALID_YESTERDAY_PHOTO_MESSAGE = 'Foto no valida. Debes enviar una foto tomada hoy.';

    private const SESSION_CLOSED_MESSAGE = "Gracias por comunicarte con nosotros. Cerramos esta conversacion por inactividad.\n\nEsperamos que te pongas en contacto nuevamente cuando necesites asistencia.";

    private const INVALID_ATTACHMENT_TYPE_MESSAGE = 'Tipo de archivo no permitido. Envia una imagen valida con extension: .jpg, .jpeg, .png, .heic o .heif.';

    private const SERVICE_HOURS_MESSAGE = "Nuestro horario de servicio es de 7:00 AM de la manana a 10:00 PM de la noche.\n\nPor favor vuelve a intentar dentro de ese horario.";

    private const SERVICE_START_HOUR = 7;

    private const SERVICE_END_HOUR = 22;

    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'heic', 'heif'];

    private const ALLOWED_IMAGE_MIME_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/heic', 'image/heif'];

    private const TABLA_AGENCIAS_REAL = 'agencias';

    private const TABLA_AGENCIAS_LOTEDOM_DELTA = 'agencias_lotedom';

    public static function sessionClosedMessage(): string
    {
        return self::SESSION_CLOSED_MESSAGE;
    }

    public function handleIncoming(string $phone, string $message, ?string $account = null, array $incoming = []): array
    {
        $normalizedPhone = $this->normalizePhone($phone);
        $normalizedAccount = $this->normalizeAccount($account);
        $message = trim($message);

        Log::debug('WhatsApp chatbot inicio', [
            'phone' => $normalizedPhone,
            'account' => $normalizedAccount,
            'message' => $message,
        ]);

        $session = ChatbotSession::firstOrCreate(
            [
                'account' => $normalizedAccount,
                'phone' => $normalizedPhone,
            ],
            [
                'step' => self::STEP_INICIO,
                'context' => [],
                'message_count' => 0,
            ]
        );

        if (Schema::hasColumn('chatbot_sessions', 'channel')) {
            $channel = trim((string) ($incoming['channel'] ?? 'whatsapp')) ?: 'whatsapp';
            $channelRecipient = trim((string) ($incoming['channel_recipient'] ?? $normalizedPhone));
            $session->channel = $channel;
            $session->channel_recipient = $channelRecipient !== '' ? $channelRecipient : null;
            $session->save();
        }

        $currentStep = $session->step ?: self::STEP_INICIO;

        if (
            ! in_array($session->step, [self::STEP_CONFIRMAR_CIERRE_SESION, self::STEP_SISTEMA, self::STEP_TICKET_BLOQUEADO], true)
            && in_array($message, ['1', '2'], true)
        ) {
            $ticket = $this->latestTokenTicketForSession($session);

            if ($ticket !== null) {
                $reply = $this->handleTokenFeedback($session, $ticket, $message);

                $session->last_message = $message;
                $session->last_interaction_at = now();
                $session->message_count = ((int) $session->message_count) + 1;
                $session->save();

                Log::debug('WhatsApp chatbot respuesta', [
                    'phone' => $normalizedPhone,
                    'account' => $normalizedAccount,
                    'from_step' => $currentStep,
                    'to_step' => $session->step,
                    'message_count' => $session->message_count,
                    'reply' => $reply,
                ]);

                return [
                    'session' => $session,
                    'reply' => $reply,
                ];
            }
        }

        if ($this->isExpired($session)) {
            Log::debug('WhatsApp chatbot sesion expirada', [
                'phone' => $normalizedPhone,
                'account' => $normalizedAccount,
                'last_interaction_at' => $session->last_interaction_at,
            ]);

            $session->step = $this->isGreeting($message) ? self::STEP_SISTEMA : self::STEP_MENU;
            $session->context = [];
            $session->last_message = $message;
            $session->last_interaction_at = now();
            $session->message_count = ((int) $session->message_count) + 1;
            $session->save();

            $reply = $session->step === self::STEP_SISTEMA ? self::SISTEMA_MESSAGE : self::MENU_MESSAGE;

            Log::debug('WhatsApp chatbot respuesta', [
                'phone' => $normalizedPhone,
                'account' => $normalizedAccount,
                'from_step' => $currentStep,
                'to_step' => $session->step,
                'message_count' => $session->message_count,
                'reply' => $reply,
            ]);

            return [
                'session' => $session,
                'reply' => $reply,
            ];
        }

        $reply = $this->resolveReply($session, $message, $incoming);

        $session->last_message = $message;
        $session->last_interaction_at = now();
        $session->message_count = ((int) $session->message_count) + 1;
        $session->save();

        Log::debug('WhatsApp chatbot respuesta', [
            'phone' => $normalizedPhone,
            'account' => $normalizedAccount,
            'from_step' => $currentStep,
            'to_step' => $session->step,
            'message_count' => $session->message_count,
            'reply' => $reply,
        ]);

        return [
            'session' => $session,
            'reply' => $reply,
        ];
    }

    private function resolveReply(ChatbotSession $session, string $message, array $incoming): string
    {
        Log::debug('WhatsApp chatbot procesando step', [
            'phone' => $session->phone,
            'step' => $session->step,
        ]);

        if ($session->step === self::STEP_CONFIRMAR_CIERRE_SESION) {
            return $this->resolverConfirmacionCierreSesion($session, $message);
        }

        if ($session->step === self::STEP_TICKET_BLOQUEADO) {
            return $this->resolverTicketBloqueado($session, $message);
        }

        if ($this->isGreeting($message)) {
            if ($this->hasOpenConversation($session)) {
                $context = is_array($session->context) ? $session->context : [];
                $previousStep = $session->step ?: self::STEP_INICIO;
                $session->step = self::STEP_CONFIRMAR_CIERRE_SESION;
                $session->context = [
                    'previous_step' => $previousStep,
                    'previous_context' => $context,
                ];

                return self::CONFIRM_CLOSE_SESSION_MESSAGE;
            }

            $session->step = self::STEP_SISTEMA;
            $session->context = [];

            return self::SISTEMA_MESSAGE;
        }

        if ($session->step === self::STEP_SISTEMA) {
            return $this->guardarSistemaYMostrarMenu($session, $message);
        }

        if (in_array($message, ['1', '2'], true)) {
            $ticket = $this->latestTokenTicketForSession($session);

            if ($ticket !== null) {
                return $this->handleTokenFeedback($session, $ticket, $message);
            }
        }

        if ($session->step === self::STEP_TICKET_NUMERO) {
            return $this->guardarTicketYEsperarImagen($session, $message);
        }

        if ($session->step === self::STEP_TICKET_IMAGEN) {
            return $this->registrarSolicitudTicketConImagen($session, $incoming);
        }

        if ($session->step === self::STEP_SG_TIPO) {
            return $this->guardarTipoServiciosGenerales($session, $message);
        }

        if ($session->step === self::STEP_SG_TERMINAL) {
            return $this->guardarTerminalServiciosGenerales($session, $message);
        }

        if ($session->step === self::STEP_SG_IMAGEN) {
            return $this->registrarRequerimientoServiciosGenerales($session, $incoming);
        }

        if ($session->step === self::STEP_MENU) {
            if ($message === '1') {
                $session->step = self::STEP_INICIO;

                return 'Nuestro horario de servicio es de 7:00 AM de la manana a 10:00 PM de la noche.';
            }

            if ($message === '2') {
                $session->step = self::STEP_INICIO;

                return '';
            }

            if ($message === '3') {
                if (! $this->isWithinServiceHours()) {
                    $this->resetSession($session);

                    return self::SERVICE_HOURS_MESSAGE;
                }

                if ($openTicket = $this->openPaymentOrCancellationTicketForSession($session)) {
                    return $this->activarBloqueoPorTicketAbierto($session, $openTicket);
                }

                $session->step = self::STEP_TICKET_NUMERO;
                $session->context = array_merge(is_array($session->context) ? $session->context : [], [
                    'categoria' => TicketSolicitud::CATEGORIA_PAGAR,
                    'categoria_label' => 'Pagar ticket',
                ]);

                return 'Indica el codigo del terminal que deseas pagar.';
            }

            if ($message === '4') {
                if (! $this->isWithinServiceHours()) {
                    $this->resetSession($session);

                    return self::SERVICE_HOURS_MESSAGE;
                }

                if ($openTicket = $this->openPaymentOrCancellationTicketForSession($session)) {
                    return $this->activarBloqueoPorTicketAbierto($session, $openTicket);
                }

                $session->step = self::STEP_TICKET_NUMERO;
                $session->context = array_merge(is_array($session->context) ? $session->context : [], [
                    'categoria' => TicketSolicitud::CATEGORIA_ANULAR,
                    'categoria_label' => 'Anular ticket',
                ]);

                return 'Indica el codigo del terminal que deseas anular.';
            }

            if ($message === '5') {
                $session->step = self::STEP_INICIO;

                return 'Recursos Humanos: escribe tu consulta y un representante te asistira.';
            }

            if ($message === '6') {
                if (! $this->isWithinServiceHours()) {
                    $this->resetSession($session);

                    return self::SERVICE_HOURS_MESSAGE;
                }

                $session->step = self::STEP_SG_TIPO;
                $session->context = is_array($session->context) ? $session->context : [];

                return "Selecciona el tipo de averia:\n\n1-No tengo internet\n2-No tengo luz\n3-Se me friso el sistema\n4-Cambiar el inversor";
            }

            return self::MENU_MESSAGE;
        }

        $session->step = self::STEP_MENU;

        return self::MENU_MESSAGE;
    }

    private function guardarSistemaYMostrarMenu(ChatbotSession $session, string $message): string
    {
        $sistemas = [
            '1' => ['sistema' => 'real', 'label' => 'Real'],
            '2' => ['sistema' => 'delta', 'label' => 'Delta'],
            '3' => ['sistema' => 'lotedom', 'label' => 'Lotedom'],
        ];

        if (! isset($sistemas[$message])) {
            return self::SISTEMA_MESSAGE;
        }

        $session->step = self::STEP_MENU;
        $session->context = $sistemas[$message];

        return self::MENU_MESSAGE;
    }

    private function guardarTicketYEsperarImagen(ChatbotSession $session, string $message): string
    {
        $terminalCodigo = trim($message);

        if ($terminalCodigo === '' || strlen($terminalCodigo) < 2) {
            return 'No pude identificar el codigo del terminal. Envia solo el codigo del terminal.';
        }

        $context = is_array($session->context) ? $session->context : [];

        if (! $this->isWithinServiceHours()) {
            $this->resetSession($session);

            return self::SERVICE_HOURS_MESSAGE;
        }

        if (! $this->terminalExisteParaSistema((string) ($context['sistema'] ?? ''), $terminalCodigo)) {
            return 'Ese id no existe, por favor escribir el id de tu agencia.';
        }

        $session->step = self::STEP_TICKET_IMAGEN;
        $session->context = array_merge($context, [
            'ticket_numero' => $terminalCodigo,
        ]);

        return "Perfecto. Codigo de terminal {$terminalCodigo} recibido.\n\nAhora envia la imagen del comprobante para registrar la solicitud.";
    }

    private function guardarTipoServiciosGenerales(ChatbotSession $session, string $message): string
    {
        $tipos = [
            '1' => ['tipo' => 'internet', 'label' => 'No tengo internet'],
            '2' => ['tipo' => 'electricidad', 'label' => 'No tengo luz'],
            '3' => ['tipo' => 'sistema_frizado', 'label' => 'Se me friso el sistema'],
            '4' => ['tipo' => 'inversor', 'label' => 'Cambiar el inversor'],
        ];

        if (! isset($tipos[$message])) {
            return "Selecciona el tipo de averia escribiendo solo el numero:\n\n1-No tengo internet\n2-No tengo luz\n3-Se me friso el sistema\n4-Cambiar el inversor";
        }

        if (! $this->isWithinServiceHours()) {
            $this->resetSession($session);

            return self::SERVICE_HOURS_MESSAGE;
        }

        $session->step = self::STEP_SG_TERMINAL;
        $session->context = array_merge(is_array($session->context) ? $session->context : [], $tipos[$message]);

        return 'Indica el codigo del terminal afectado.';
    }

    private function guardarTerminalServiciosGenerales(ChatbotSession $session, string $message): string
    {
        $terminalCodigo = trim($message);

        if ($terminalCodigo === '' || strlen($terminalCodigo) < 2) {
            return 'No pude identificar el codigo del terminal. Envia solo el codigo del terminal afectado.';
        }

        $context = is_array($session->context) ? $session->context : [];

        if (! $this->isWithinServiceHours()) {
            $this->resetSession($session);

            return self::SERVICE_HOURS_MESSAGE;
        }

        if (! $this->terminalExisteParaSistema((string) ($context['sistema'] ?? ''), $terminalCodigo)) {
            return 'Ese id no existe, por favor escribir el id de tu agencia.';
        }

        $session->step = self::STEP_SG_IMAGEN;
        $session->context = array_merge($context, [
            'terminal_codigo' => $terminalCodigo,
        ]);

        return "Perfecto. Terminal {$terminalCodigo} recibido.\n\nAhora envia la imagen de la averia para registrar la solicitud.";
    }

    private function registrarRequerimientoServiciosGenerales(ChatbotSession $session, array $incoming): string
    {
        $context = is_array($session->context) ? $session->context : [];
        $tipo = (string) ($context['tipo'] ?? '');
        $tipoLabel = (string) ($context['label'] ?? '');
        $terminalCodigo = trim((string) ($context['terminal_codigo'] ?? ''));
        $attachmentUrl = $this->normalizeAttachmentUrl($incoming['attachment_url'] ?? null);
        $attachmentMessageId = $this->normalizeMessageId($incoming['message_id'] ?? null);

        if ($tipo === '' || $terminalCodigo === '') {
            $this->resetSession($session);

            return 'Perdi el contexto de la solicitud. Por favor inicia de nuevo y elige la opcion 6.';
        }

        if (! $this->isWithinServiceHours()) {
            $this->resetSession($session);

            return self::SERVICE_HOURS_MESSAGE;
        }

        if ($attachmentUrl === null) {
            return 'Necesito que envies una imagen para continuar con el registro de la averia.';
        }

        if (! $this->isAllowedImageAttachment($incoming)) {
            return self::INVALID_ATTACHMENT_TYPE_MESSAGE;
        }

        if ($this->isYesterdayAttachment($incoming['attachment_timestamp'] ?? null)) {
            return self::INVALID_YESTERDAY_PHOTO_MESSAGE;
        }

        try {
            $requerimientoData = [
                'user_id' => $this->chatbotUserId(),
                'whatsapp_phone' => $session->phone,
                'tipo' => $tipo,
                'titulo' => 'Averia',
                'descripcion' => 'Solicitud recibida por '.ucfirst((string) ($session->channel ?? 'whatsapp'))
                    .".\n\nTipo: {$tipoLabel}\nTerminal: {$terminalCodigo}",
                'prioridad' => 'media',
                'estado' => 'pendiente',
                'progreso' => 0,
                'attachment_url' => $attachmentUrl,
                'attachment_message_id' => $attachmentMessageId,
            ];

            if (Schema::hasColumn('servicios_generales_requerimientos', 'source_channel')) {
                $requerimientoData['source_channel'] = $session->channel ?? 'whatsapp';
                $requerimientoData['source_recipient'] = $session->channel_recipient ?? $session->phone;
            }

            $requerimiento = ServicioGeneralRequerimiento::create($requerimientoData);
        } catch (\Throwable $e) {
            Log::error('WhatsApp chatbot error registrando requerimiento servicios generales', [
                'phone' => $session->phone,
                'tipo' => $tipo,
                'terminal_codigo' => $terminalCodigo,
                'attachment_url' => $attachmentUrl,
                'message' => $e->getMessage(),
            ]);

            $this->resetSession($session);

            return 'No pude registrar la averia en este momento. Por favor intenta mas tarde.';
        }

        $this->resetSession($session);

        return "Solicitud registrada correctamente.\n\nCodigo: {$requerimiento->ticket_codigo}\nTipo: {$tipoLabel}\nTerminal: {$terminalCodigo}\nImagen: Recibida\nEstado: Pendiente";
    }

    private function chatbotUserId(): int
    {
        $user = User::firstOrCreate(
            ['email' => 'chatbot@bsh.local'],
            [
                'name' => 'Chatbot BSH',
                'password' => Hash::make(Str::random(32)),
            ]
        );

        return (int) $user->id;
    }

    private function registrarSolicitudTicketConImagen(ChatbotSession $session, array $incoming): string
    {
        $context = is_array($session->context) ? $session->context : [];
        $ticketNumero = trim((string) ($context['ticket_numero'] ?? ''));
        $categoria = $context['categoria'] ?? TicketSolicitud::CATEGORIA_PAGAR;
        $categoriaLabel = $context['categoria_label'] ?? 'Pagar ticket';
        $attachmentUrl = $this->normalizeAttachmentUrl($incoming['attachment_url'] ?? null);
        $attachmentMessageId = $this->normalizeMessageId($incoming['message_id'] ?? null);

        if ($ticketNumero === '') {
            $this->resetSession($session);

            return 'Perdi el contexto de la solicitud. Por favor inicia de nuevo y elige la opcion 3 o 4.';
        }

        if (! $this->isWithinServiceHours()) {
            $this->resetSession($session);

            return self::SERVICE_HOURS_MESSAGE;
        }

        if ($attachmentUrl === null) {
            return 'Necesito que envies una imagen para continuar con el registro del ticket.';
        }

        if (! $this->isAllowedImageAttachment($incoming)) {
            return self::INVALID_ATTACHMENT_TYPE_MESSAGE;
        }

        if ($this->isYesterdayAttachment($incoming['attachment_timestamp'] ?? null)) {
            return self::INVALID_YESTERDAY_PHOTO_MESSAGE;
        }

        if ($openTicket = $this->openPaymentOrCancellationTicketForSession($session)) {
            return $this->activarBloqueoPorTicketAbierto($session, $openTicket);
        }

        try {
            $ticketData = [
                'phone' => $session->phone,
                'categoria' => $categoria,
                'ticket_numero' => $ticketNumero,
                'estado' => TicketSolicitud::ESTADO_PENDIENTE,
                'mensaje_original' => $categoriaLabel.': '.$ticketNumero,
                'attachment_url' => $attachmentUrl,
                'attachment_message_id' => $attachmentMessageId,
            ];

            if (Schema::hasColumn('ticket_solicitudes', 'source_channel')) {
                $ticketData['source_channel'] = $session->channel ?? 'whatsapp';
                $ticketData['source_recipient'] = $session->channel_recipient ?? $session->phone;
            }

            $solicitud = TicketSolicitud::create($ticketData);
        } catch (\Throwable $e) {
            Log::error('WhatsApp chatbot error registrando ticket', [
                'phone' => $session->phone,
                'categoria' => $categoria,
                'ticket_numero' => $ticketNumero,
                'attachment_url' => $attachmentUrl,
                'message' => $e->getMessage(),
            ]);

            $this->resetSession($session);

            return 'No pude registrar la solicitud en este momento. Por favor intenta mas tarde.';
        }

        $this->resetSession($session);

        return "Solicitud registrada correctamente.\n\nCodigo: {$solicitud->codigo}\nCategoria: {$solicitud->categoria_label}\nTerminal: {$solicitud->ticket_numero}\nImagen: Recibida\nEstado: Pendiente";
    }

    private function latestTokenTicketForSession(ChatbotSession $session): ?TicketSolicitud
    {
        if (! Schema::hasTable('ticket_solicitudes')) {
            return null;
        }

        return TicketSolicitud::query()
            ->where('phone', $session->phone)
            ->when(
                Schema::hasColumn('ticket_solicitudes', 'source_channel'),
                fn ($query) => $query->where('source_channel', $session->channel ?? 'whatsapp')
            )
            ->where('categoria', TicketSolicitud::CATEGORIA_PAGAR)
            ->where('estado', TicketSolicitud::ESTADO_TOKEN_ENVIADO)
            ->latest()
            ->first();
    }

    private function openPaymentOrCancellationTicketForSession(ChatbotSession $session): ?TicketSolicitud
    {
        if (! Schema::hasTable('ticket_solicitudes')) {
            return null;
        }

        return TicketSolicitud::query()
            ->where('phone', $session->phone)
            ->when(
                Schema::hasColumn('ticket_solicitudes', 'source_channel'),
                fn ($query) => $query->where('source_channel', $session->channel ?? 'whatsapp')
            )
            ->whereIn('categoria', [
                TicketSolicitud::CATEGORIA_PAGAR,
                TicketSolicitud::CATEGORIA_ANULAR,
            ])
            ->whereNotIn('estado', [
                TicketSolicitud::ESTADO_PAGADO,
                TicketSolicitud::ESTADO_NULO,
                TicketSolicitud::ESTADO_RECHAZADO,
            ])
            ->latest()
            ->first();
    }

    private function openTicketBlockMessage(TicketSolicitud $ticket): string
    {
        return "Ya tienes una solicitud abierta.\n\n"
            ."Codigo: {$ticket->codigo}\n"
            ."Categoria: {$ticket->categoria_label}\n"
            ."Terminal: {$ticket->ticket_numero}\n"
            ."Estado: {$ticket->estado_label}\n\n"
            ."1- Ver Solicitud Pendiente\n"
            .'2- Rechazar solicitud';
    }

    private function activarBloqueoPorTicketAbierto(ChatbotSession $session, TicketSolicitud $ticket): string
    {
        $session->step = self::STEP_TICKET_BLOQUEADO;
        $session->context = [
            'blocked_ticket_id' => (int) $ticket->id,
        ];

        return $this->openTicketBlockMessage($ticket);
    }

    private function resolverTicketBloqueado(ChatbotSession $session, string $message): string
    {
        $context = is_array($session->context) ? $session->context : [];
        $ticketId = (int) ($context['blocked_ticket_id'] ?? 0);
        $ticket = $ticketId > 0 ? TicketSolicitud::query()->find($ticketId) : null;

        if ($ticket === null || $ticket->phone !== $session->phone) {
            $this->resetSession($session);

            return 'No pude encontrar la solicitud pendiente. Escribe hola para iniciar nuevamente.';
        }

        if (in_array((string) $ticket->estado, [
            TicketSolicitud::ESTADO_PAGADO,
            TicketSolicitud::ESTADO_NULO,
            TicketSolicitud::ESTADO_RECHAZADO,
        ], true)) {
            $this->resetSession($session);

            return "Tu solicitud {$ticket->codigo} ya no esta abierta.\n\nEscribe hola para iniciar nuevamente.";
        }

        if ($message === '1') {
            return $this->openTicketBlockMessage($ticket);
        }

        if ($message === '2') {
            $ticket->estado = TicketSolicitud::ESTADO_RECHAZADO;
            $ticket->procesado_por_id = $this->chatbotUserId();
            $ticket->procesado_at = now();
            $ticket->tomado_por_id = null;
            $ticket->tomado_at = null;
            $ticket->notas = $this->appendTicketNote(
                (string) $ticket->notas,
                'Rechazado por el usuario'
            );
            $ticket->save();

            $this->resetSession($session);

            return "Tu solicitud {$ticket->codigo} fue rechazada correctamente.\n\nYa puedes crear una nueva solicitud si la necesitas.";
        }

        return $this->openTicketBlockMessage($ticket);
    }

    private function handleTokenFeedback(ChatbotSession $session, TicketSolicitud $ticket, string $message): string
    {
        $session->step = self::STEP_INICIO;
        $session->context = [];

        if ($message === '1') {
            $ticket->estado = TicketSolicitud::ESTADO_TICKET_PAGADO;
            $ticket->procesado_por_id = null;
            $ticket->procesado_at = null;
            $ticket->notas = $this->appendTicketNote(
                (string) $ticket->notas,
                'Cliente confirmo que el token funciono. El usuario del sistema debe cerrar el ticket.'
            );
            $ticket->save();

            $this->notifySupportForTokenFeedback($ticket, 'El cliente confirmo que el token funciono. El estado cambio a Ticket pagado Por otra Terminal. El usuario del sistema debe cerrar el ticket.');

            return "Gracias por confirmar.\n\nTu ticket fue marcado como Ticket pagado Por otra Terminal. Soporte debe cerrar el ticket en el sistema.";
        }

        $ticket->estado = TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO;
        $ticket->procesado_por_id = null;
        $ticket->procesado_at = null;
        $ticket->notas = $this->appendTicketNote(
            (string) $ticket->notas,
            'Cliente indico que el token no funciono. Se reabre para enviar un nuevo token.'
        );
        $ticket->save();

        $this->notifySupportForTokenFeedback($ticket, 'El cliente indico que el token no funciono. Enviar un nuevo token.');

        return "Entendido.\n\nMarcamos tu solicitud como Token No Funciono para que soporte te envie un nuevo token.";
    }

    private function appendTicketNote(string $currentNotes, string $newNote): string
    {
        $line = now()->format('d/m/Y h:i A').' - '.$newNote;
        $currentNotes = trim($currentNotes);

        return $currentNotes !== '' ? $currentNotes."\n".$line : $line;
    }

    private function notifySupportForTokenFeedback(TicketSolicitud $ticket, string $detail): void
    {
        $recipients = config('services.whatsapp.support_recipients', []);

        if (empty($recipients)) {
            Log::warning('WhatsApp chatbot soporte sin destinatarios para feedback de token', [
                'ticket_id' => $ticket->id,
                'ticket_codigo' => $ticket->codigo,
                'detail' => $detail,
            ]);

            return;
        }

        $message = "Ticket {$ticket->codigo}\n\n"
            ."{$detail}\n"
            ."Terminal: {$ticket->ticket_numero}\n"
            ."Telefono: {$ticket->phone}";

        foreach ($recipients as $recipient) {
            $recipient = trim((string) $recipient);

            if ($recipient === '') {
                continue;
            }

            try {
                app(WhatsAppService::class)->sendText($recipient, $message);
            } catch (\Throwable $e) {
                Log::error('WhatsApp chatbot error notificando soporte por feedback de token', [
                    'ticket_id' => $ticket->id,
                    'recipient' => $recipient,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function resolverConfirmacionCierreSesion(ChatbotSession $session, string $message): string
    {
        $context = is_array($session->context) ? $session->context : [];
        $previousStep = (string) ($context['previous_step'] ?? self::STEP_MENU);
        $previousContext = is_array($context['previous_context'] ?? null) ? $context['previous_context'] : [];

        if ($message === '1') {
            $this->resetSession($session);

            return "Sesion cerrada correctamente.\n\nEscribe hola para iniciar nuevamente.";
        }

        if ($message === '2') {
            $session->step = $previousStep;
            $session->context = $previousContext;

            return "Retomamos tu solicitud donde la dejaste.\n\n".$this->promptForStep($session);
        }

        return self::CONFIRM_CLOSE_SESSION_MESSAGE;
    }

    private function promptForStep(ChatbotSession $session): string
    {
        $context = is_array($session->context) ? $session->context : [];
        $categoriaLabel = (string) ($context['categoria_label'] ?? 'ticket');
        $terminalCodigo = trim((string) ($context['ticket_numero'] ?? $context['terminal_codigo'] ?? ''));

        return match ($session->step) {
            self::STEP_SISTEMA => self::SISTEMA_MESSAGE,
            self::STEP_MENU => self::MENU_MESSAGE,
            self::STEP_TICKET_BLOQUEADO => 'Tienes una solicitud abierta. Escribe 1 para verla o 2 para rechazarla.',
            self::STEP_TICKET_NUMERO => "Estas creando una solicitud de {$categoriaLabel}.\n\nIndica el codigo del terminal.",
            self::STEP_TICKET_IMAGEN => $terminalCodigo !== ''
                ? "Estas creando una solicitud de {$categoriaLabel} para el terminal {$terminalCodigo}.\n\nEnvia la imagen del comprobante para registrar la solicitud."
                : "Estas creando una solicitud de {$categoriaLabel}.\n\nEnvia la imagen del comprobante para registrar la solicitud.",
            self::STEP_SG_TIPO => "Selecciona el tipo de averia:\n\n1-No tengo internet\n2-No tengo luz\n3-Se me friso el sistema\n4-Cambiar el inversor",
            self::STEP_SG_TERMINAL => 'Estas reportando una averia.\n\nIndica el codigo del terminal afectado.',
            self::STEP_SG_IMAGEN => $terminalCodigo !== ''
                ? "Estas reportando una averia para el terminal {$terminalCodigo}.\n\nEnvia una imagen de la averia para registrar la solicitud."
                : 'Estas reportando una averia.\n\nEnvia una imagen de la averia para registrar la solicitud.',
            default => self::MENU_MESSAGE,
        };
    }

    private function hasOpenConversation(ChatbotSession $session): bool
    {
        return ! in_array($session->step ?: self::STEP_INICIO, [
            self::STEP_INICIO,
            self::STEP_CONFIRMAR_CIERRE_SESION,
        ], true);
    }

    private function normalizeAttachmentUrl(mixed $attachment): ?string
    {
        if (! is_string($attachment)) {
            return null;
        }

        $attachment = trim($attachment);

        if ($attachment === '' || in_array(strtolower($attachment), ['false', 'null'], true)) {
            return null;
        }

        return $attachment;
    }

    private function normalizeMessageId(mixed $messageId): ?string
    {
        if ($messageId === null || is_array($messageId)) {
            return null;
        }

        $messageId = trim((string) $messageId);

        return $messageId !== '' ? $messageId : null;
    }

    private function isAllowedImageAttachment(array $incoming): bool
    {
        $extension = $this->normalizeFileExtension($incoming['attachment_extension'] ?? null)
            ?? $this->extractExtensionFromPath($incoming['attachment_filename'] ?? null)
            ?? $this->extractExtensionFromPath($incoming['attachment_url'] ?? null);
        $mime = $this->normalizeMimeType($incoming['attachment_mime'] ?? null);
        $type = $this->normalizeAttachmentType($incoming['attachment_type'] ?? null);

        if ($type !== null && ! in_array($type, ['image', 'photo'], true)) {
            return false;
        }

        if ($extension !== null && ! in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            return false;
        }

        if ($mime !== null && ! in_array($mime, self::ALLOWED_IMAGE_MIME_TYPES, true)) {
            return false;
        }

        return $extension !== null || $mime !== null || in_array($type, ['image', 'photo'], true);
    }

    private function normalizeFileExtension(mixed $extension): ?string
    {
        if ($extension === null || is_array($extension)) {
            return null;
        }

        $extension = strtolower(ltrim(trim((string) $extension), '.'));

        return $extension !== '' && ! in_array($extension, ['false', 'null'], true) ? $extension : null;
    }

    private function extractExtensionFromPath(mixed $path): ?string
    {
        if ($path === null || is_array($path)) {
            return null;
        }

        $path = trim((string) $path);

        if ($path === '' || in_array(strtolower($path), ['false', 'null'], true)) {
            return null;
        }

        $path = parse_url($path, PHP_URL_PATH) ?: $path;

        return $this->normalizeFileExtension(pathinfo($path, PATHINFO_EXTENSION));
    }

    private function normalizeMimeType(mixed $mime): ?string
    {
        if ($mime === null || is_array($mime)) {
            return null;
        }

        $mime = strtolower(trim(explode(';', (string) $mime)[0]));

        if (in_array($mime, ['application/octet-stream', 'binary/octet-stream'], true)) {
            return null;
        }

        return $mime !== '' && ! in_array($mime, ['false', 'null'], true) ? $mime : null;
    }

    private function normalizeAttachmentType(mixed $type): ?string
    {
        if ($type === null || is_array($type)) {
            return null;
        }

        $type = strtolower(trim((string) $type));

        if ($type === '' || in_array($type, ['false', 'null'], true)) {
            return null;
        }

        return in_array($type, ['image', 'photo', 'document', 'file', 'video', 'audio', 'sticker'], true)
            ? $type
            : null;
    }

    private function isYesterdayAttachment(mixed $timestamp): bool
    {
        $date = $this->parseAttachmentTimestamp($timestamp);

        return $date !== null && $date->isSameDay(now()->subDay());
    }

    private function parseAttachmentTimestamp(mixed $timestamp): ?Carbon
    {
        if ($timestamp === null || is_array($timestamp)) {
            return null;
        }

        if (is_numeric($timestamp)) {
            $numericTimestamp = (int) $timestamp;

            if ($numericTimestamp <= 0) {
                return null;
            }

            if ($numericTimestamp > 9999999999) {
                $numericTimestamp = intdiv($numericTimestamp, 1000);
            }

            return Carbon::createFromTimestamp($numericTimestamp);
        }

        $timestamp = trim((string) $timestamp);

        if ($timestamp === '' || in_array(strtolower($timestamp), ['false', 'null'], true)) {
            return null;
        }

        try {
            return Carbon::parse($timestamp);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resetSession(ChatbotSession $session): void
    {
        $session->step = self::STEP_INICIO;
        $session->context = [];
    }

    private function isWithinServiceHours(): bool
    {
        $now = now();
        $start = $now->copy()->setTime(self::SERVICE_START_HOUR, 0);
        $end = $now->copy()->setTime(self::SERVICE_END_HOUR, 0);

        return $now->greaterThanOrEqualTo($start) && $now->lessThan($end);
    }

    private function terminalRealExiste(string $terminalCodigo): bool
    {
        return $this->terminalExisteEnTabla(self::TABLA_AGENCIAS_REAL, $terminalCodigo);
    }

    private function terminalLotedomExiste(string $terminalCodigo): bool
    {
        return $this->terminalExisteEnCatalogoLotedomDelta($terminalCodigo, 'lotedom');
    }

    private function terminalDeltaExiste(string $terminalCodigo): bool
    {
        return $this->terminalExisteEnCatalogoLotedomDelta($terminalCodigo, 'delta');
    }

    private function terminalExisteParaSistema(string $sistema, string $terminalCodigo): bool
    {
        return match (Str::lower(trim($sistema))) {
            'delta' => $this->terminalDeltaExiste($terminalCodigo),
            'lotedom' => $this->terminalLotedomExiste($terminalCodigo),
            default => $this->terminalRealExiste($terminalCodigo),
        };
    }

    private function terminalExisteEnCatalogoLotedomDelta(string $terminalCodigo, string $sistema): bool
    {
        $tabla = self::TABLA_AGENCIAS_LOTEDOM_DELTA;

        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'terminal')) {
            return false;
        }

        $terminalNormalizado = $this->normalizarTerminalReal($terminalCodigo);

        if ($terminalNormalizado === '') {
            return false;
        }

        $hasSistema = Schema::hasColumn($tabla, 'sistema');
        $hasEmpresa = Schema::hasColumn($tabla, 'empresa');

        if (! $hasSistema && ! $hasEmpresa) {
            return false;
        }

        $query = DB::table($tabla)->whereNotNull('terminal');

        if (Schema::hasColumn($tabla, 'estatus')) {
            $query->where('estatus', 1);
        }

        $sistema = Str::lower(trim($sistema));
        $columns = ['terminal'];

        if ($hasSistema) {
            $columns[] = 'sistema';
        }

        if ($hasEmpresa) {
            $columns[] = 'empresa';
        }

        return $query
            ->get($columns)
            ->contains(function ($row) use ($terminalNormalizado, $sistema, $hasSistema, $hasEmpresa) {
                if ($this->normalizarTerminalReal((string) ($row->terminal ?? '')) !== $terminalNormalizado) {
                    return false;
                }

                $sistemaValor = $hasSistema ? Str::lower((string) ($row->sistema ?? '')) : '';
                $empresaValor = $hasEmpresa ? Str::lower((string) ($row->empresa ?? '')) : '';

                if ($sistema === 'delta') {
                    return str_contains($sistemaValor, 'delta') || str_contains($empresaValor, 'delta');
                }

                if ($sistema === 'lotedom') {
                    return str_contains($sistemaValor, 'lotedom') || str_contains($empresaValor, 'lotedom');
                }

                return false;
            });
    }

    private function terminalExisteEnTabla(string $tabla, string $terminalCodigo): bool
    {
        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'terminal')) {
            return false;
        }

        $terminalNormalizado = $this->normalizarTerminalReal($terminalCodigo);

        if ($terminalNormalizado === '') {
            return false;
        }

        $query = $tabla === 'agencias'
            ? Agencia::query()->whereNotNull('terminal')
            : DB::table($tabla)->whereNotNull('terminal');

        if (Schema::hasColumn($tabla, 'estatus')) {
            $query->where('estatus', 1);
        }

        return $query
            ->pluck('terminal')
            ->contains(fn ($terminal) => $this->normalizarTerminalReal((string) $terminal) === $terminalNormalizado);
    }

    private function normalizarTerminalReal(string $terminalCodigo): string
    {
        $terminalCodigo = trim($terminalCodigo);

        if ($terminalCodigo === '') {
            return '';
        }

        $terminalCodigo = ltrim($terminalCodigo, '0');

        return $terminalCodigo === '' ? '0' : $terminalCodigo;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function normalizeAccount(?string $account): string
    {
        $account = trim((string) $account);

        return $account !== '' ? $account : 'default';
    }

    private function isGreeting(string $message): bool
    {
        return Str::lower(trim($message)) === 'hola';
    }

    private function isExpired(ChatbotSession $session): bool
    {
        if (! $session->last_interaction_at) {
            return false;
        }

        return Carbon::parse($session->last_interaction_at)->lt(now()->subMinute());
    }
}
