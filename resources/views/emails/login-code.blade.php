<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код для входа</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,0.08);">
                <tr>
                    <td style="padding:24px 24px 12px;font-size:22px;font-weight:700;color:#0f172a;">
                        Вход в приложение
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 8px;font-size:15px;line-height:1.6;">
                        Используйте этот код подтверждения для входа. Никому его не сообщайте.
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 24px 8px;">
                        <div style="display:inline-block;padding:12px 18px;background:#eef2ff;border-radius:10px;font-size:28px;letter-spacing:8px;font-weight:700;color:#1d4ed8;">
                            {{ $code }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 24px 24px;font-size:14px;line-height:1.6;color:#4b5563;">
                        Код действует 15 минут. Если вы не запрашивали вход, просто проигнорируйте это письмо.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
