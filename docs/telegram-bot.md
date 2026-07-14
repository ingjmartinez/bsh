# Bot de Telegram

El canal de Telegram reutiliza el flujo del chatbot existente: seleccion de sistema, menus, tickets, averias, imagenes, seguimiento y cierre por inactividad.

## Configuracion

1. Crea o rota el token del bot en `@BotFather`.
2. Configura directamente en el `.env` del servidor:

```dotenv
APP_URL=https://crm.ejemplo.com
TELEGRAM_BOT_TOKEN=token_nuevo_de_botfather
TELEGRAM_WEBHOOK_SECRET=secreto_aleatorio_sin_espacios
TELEGRAM_BOT_USERNAME=nombre_del_bot_sin_arroba
TELEGRAM_API_URL=https://api.telegram.org
TELEGRAM_TIMEOUT=30
TELEGRAM_VERIFY_SSL=true
```

El secreto del webhook solo admite letras, numeros, guion y guion bajo. No debe ser igual al token del bot.

## Activacion

Ejecuta en produccion:

```shell
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan telegram:webhook:set
```

Tambien se puede indicar una URL distinta:

```shell
php artisan telegram:webhook:set --url=https://crm.ejemplo.com/api/telegram/webhook
```

El endpoint registrado es `POST /api/telegram/webhook`. Debe ser accesible publicamente mediante HTTPS.

## Flujo de identificacion

Telegram no entrega automaticamente el telefono. La primera vez, el bot muestra el boton **Compartir mi numero de telefono**. Solo acepta un contacto cuyo `user_id` corresponda al remitente; luego conserva la asociacion entre telefono y `chat_id`.

Las fotos y documentos recibidos se descargan en el disco `public`, dentro de `chatbot/telegram`. Esto evita persistir enlaces temporales de Telegram que contienen el token del bot.
