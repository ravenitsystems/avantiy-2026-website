<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation – {{ $teamName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0f0f0f; font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #0f0f0f;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%;">
                    <tr>
                        <td align="center" style="padding: 0 0 32px 0;">
                            <span style="font-size: 24px; font-weight: 600; color: rgb(139, 61, 255);">Avantiy</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #1a1a1a; border: 1px solid #334155; border-radius: 12px; padding: 40px; box-sizing: border-box;">
                            <h1 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 600; color: #e2e8f0;">
                                You've been invited to join a team
                            </h1>
                            <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 1.6; color: rgb(100, 116, 139);">
                                <strong style="color: #e2e8f0;">{{ $inviterName }}</strong> has invited you to join <strong style="color: #e2e8f0;">{{ $teamName }}</strong> on Avantiy.
                                @if($roleName)
                                    You'll be added as <strong style="color: rgb(139, 61, 255);">{{ $roleName }}</strong>.
                                @endif
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 0 24px 0;">
                                <tr>
                                    <td>
                                        <a href="{{ $acceptUrl }}" style="display: inline-block; padding: 14px 28px; background-color: rgb(139, 61, 255); color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 600; border-radius: 8px;">
                                            Accept invitation
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0; font-size: 13px; color: rgb(100, 116, 139);">
                                This invitation expires in 7 days. If you didn't expect this email, you can safely ignore it.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 24px 0 0 0;">
                            <p style="margin: 0; font-size: 12px; color: rgb(100, 116, 139);">
                                &copy; {{ date('Y') }} Avantiy. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
