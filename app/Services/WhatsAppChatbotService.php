<?php

namespace App\Services;

use App\Models\ChatbotSession;
use App\Models\ServicioGeneralRequerimiento;
use App\Models\TicketSolicitud;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppChatbotService
{
    private const STEP_INICIO = 'inicio';
    private const STEP_MENU = 'consulta_hora_menu';
    private const STEP_TICKET_NUMERO = 'ticket_numero';
    private const STEP_TICKET_IMAGEN = 'ticket_imagen';
    private const STEP_SG_TIPO = 'servicios_generales_tipo';
    private const STEP_SG_TERMINAL = 'servicios_generales_terminal';
    private const STEP_SG_IMAGEN = 'servicios_generales_imagen';

    private const MENU_MESSAGE = "Hola. Soy el asistente virtual de BSH, comprometido contigo siempre.\n\nPara continuar, escribe solo el numero de la opcion que necesitas:\n\n1-Consultar horario de servicio\n2-Consultar servicios disponibles\n3-Pagar ticket\n4-Anular ticket\n5-Recursos Humanos\n6-Reportar averia\n\nEstoy listo para ayudarte.";
    private const SESSION_CLOSED_MESSAGE = "Gracias por comunicarte con nosotros. Cerramos esta conversacion por inactividad.\n\nEsperamos que te pongas en contacto nuevamente cuando necesites asistencia.";

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

        $currentStep = $session->step ?: self::STEP_INICIO;

        if ($this->isExpired($session)) {
            Log::debug('WhatsApp chatbot sesion expirada', [
                'phone' => $normalizedPhone,
                'account' => $normalizedAccount,
                'last_interaction_at' => $session->last_interaction_at,
            ]);

            $session->step = self::STEP_MENU;
            $session->context = [];
            $session->last_message = $message;
            $session->last_interaction_at = now();
            $session->message_count = ((int) $session->message_count) + 1;
            $session->save();

            $reply = self::SESSION_CLOSED_MESSAGE . "\n\n" . self::MENU_MESSAGE;

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

                return '7:00 am a 9:00 pm';
            }

            if ($message === '2') {
                $session->step = self::STEP_INICIO;

                return '';
            }

            if ($message === '3') {
                $session->step = self::STEP_TICKET_NUMERO;
                $session->context = [
                    'categoria' => TicketSolicitud::CATEGORIA_PAGAR,
                    'categoria_label' => 'Pagar ticket',
                ];

                return 'Indica el codigo del terminal que deseas pagar.';
            }

            if ($message === '4') {
                $session->step = self::STEP_TICKET_NUMERO;
                $session->context = [
                    'categoria' => TicketSolicitud::CATEGORIA_ANULAR,
                    'categoria_label' => 'Anular ticket',
                ];

                return 'Indica el codigo del terminal que deseas anular.';
            }

            if ($message === '5') {
                $session->step = self::STEP_INICIO;

                return 'Recursos Humanos: escribe tu consulta y un representante te asistira.';
            }

            if ($message === '6') {
                $session->step = self::STEP_SG_TIPO;
                $session->context = [];

                return "Selecciona el tipo de averia:\n\n1-No tengo internet\n2-No tengo luz\n3-Se me friso el sistema\n4-Cambiar el inversor";
            }

            return self::MENU_MESSAGE;
        }

        $session->step = self::STEP_MENU;

        return self::MENU_MESSAGE;
    }

    private function guardarTicketYEsperarImagen(ChatbotSession $session, string $message): string
    {
        $terminalCodigo = trim($message);

        if ($terminalCodigo === '' || strlen($terminalCodigo) < 2) {
            return 'No pude identificar el codigo del terminal. Envia solo el codigo del terminal.';
        }

        $context = is_array($session->context) ? $session->context : [];
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

        if (!isset($tipos[$message])) {
            return "Selecciona el tipo de averia escribiendo solo el numero:\n\n1-No tengo internet\n2-No tengo luz\n3-Se me friso el sistema\n4-Cambiar el inversor";
        }

        $session->step = self::STEP_SG_TERMINAL;
        $session->context = $tipos[$message];

        return 'Indica el codigo del terminal afectado.';
    }

    private function guardarTerminalServiciosGenerales(ChatbotSession $session, string $message): string
    {
        $terminalCodigo = trim($message);

        if ($terminalCodigo === '' || strlen($terminalCodigo) < 2) {
            return 'No pude identificar el codigo del terminal. Envia solo el codigo del terminal afectado.';
        }

        $context = is_array($session->context) ? $session->context : [];
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

        if ($attachmentUrl === null) {
            return 'Necesito que envies una imagen para continuar con el registro de la averia.';
        }

        try {
            $requerimiento = ServicioGeneralRequerimiento::create([
                'user_id' => $this->chatbotUserId(),
                'whatsapp_phone' => $session->phone,
                'tipo' => $tipo,
                'titulo' => 'Averia',
                'descripcion' => "Solicitud recibida por WhatsApp.\n\nTipo: {$tipoLabel}\nTerminal: {$terminalCodigo}",
                'prioridad' => 'media',
                'estado' => 'pendiente',
                'progreso' => 0,
                'attachment_url' => $attachmentUrl,
                'attachment_message_id' => $attachmentMessageId,
            ]);
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

        if ($attachmentUrl === null) {
            return 'Necesito que envies una imagen para continuar con el registro del ticket.';
        }

        try {
            $solicitud = TicketSolicitud::create([
                'phone' => $session->phone,
                'categoria' => $categoria,
                'ticket_numero' => $ticketNumero,
                'estado' => TicketSolicitud::ESTADO_PENDIENTE,
                'mensaje_original' => $categoriaLabel . ': ' . $ticketNumero,
                'attachment_url' => $attachmentUrl,
                'attachment_message_id' => $attachmentMessageId,
            ]);
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

    private function normalizeAttachmentUrl(mixed $attachment): ?string
    {
        if (!is_string($attachment)) {
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

    private function resetSession(ChatbotSession $session): void
    {
        $session->step = self::STEP_INICIO;
        $session->context = [];
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

    private function isExpired(ChatbotSession $session): bool
    {
        if (!$session->last_interaction_at) {
            return false;
        }

        return Carbon::parse($session->last_interaction_at)->lt(now()->subMinute());
    }
}
