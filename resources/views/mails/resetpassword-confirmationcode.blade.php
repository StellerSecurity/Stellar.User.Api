<!doctype html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Reset Password</title>

    <!--[if mso]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->

    <style>
        /* Minimal, email-safe. Most styling is inline. */
        @media (max-width: 640px) {
            .container { width: 100% !important; }
            .px { padding-left: 18px !important; padding-right: 18px !important; }
            .code { font-size: 28px !important; letter-spacing: 3px !important; }
        }
    </style>
</head>

<body style="margin:0; padding:0; background:#060A14;">
<!-- Preheader (hidden) -->
<div style="display:none; font-size:1px; color:#060A14; line-height:1px; max-height:0px; max-width:0px; opacity:0; overflow:hidden;">
    Your Stellar ID password reset code is inside. It expires in 24 hours.
</div>

<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse; background:#060A14;">
    <tr>
        <td align="center" style="padding:28px 12px;">
            <!-- Outer container -->
            <table role="presentation" cellpadding="0" cellspacing="0" width="600" class="container" style="width:600px; max-width:600px; border-collapse:separate; border-spacing:0;">
                <!-- Top brand bar -->
                <tr>
                    <td style="padding:0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate; border-spacing:0; background:#0B1022; border:1px solid #142044; border-bottom:0; border-radius:18px 18px 0 0;">
                            <tr>
                                <td class="px" style="padding:18px 24px;">
                                    <div style="font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; color:#EAF0FF; font-size:14px; letter-spacing:0.6px; font-weight:700;">
                                        STELLAR SECURITY
                                    </div>
                                    <div style="font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; color:#9FB2E8; font-size:12px; margin-top:4px;">
                                        Stellar ID
                                    </div>
                                </td>
                                <td align="right" class="px" style="padding:18px 24px;">
                                    <div style="font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; color:#9FB2E8; font-size:12px;">
                                        Security notice
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="height:4px; background:#0B1022; padding:0 24px 0 24px;">
                                    <div style="height:4px; border-radius:999px; background:#1E2A57;">
                                        <div style="height:4px; width:44%; border-radius:999px; background:#2B63FF;"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr><td colspan="2" style="height:16px; font-size:1px; line-height:1px;">&nbsp;</td></tr>
                        </table>
                    </td>
                </tr>

                <!-- Card -->
                <tr>
                    <td style="padding:0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate; border-spacing:0; background:#FFFFFF; border:1px solid #142044; border-top:0; border-radius:0 0 18px 18px;">
                            <tr>
                                <td class="px" style="padding:26px 24px 10px 24px;">
                                    <h1 style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:22px; line-height:1.25; color:#0B1022;">
                                        Reset your password
                                    </h1>
                                    <p style="margin:10px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:14px; line-height:1.6; color:#394056;">
                                        You requested a password reset for your <strong style="color:#0B1022;">Stellar ID</strong>.
                                        Use the confirmation code below to continue.
                                    </p>
                                </td>
                            </tr>

                            <!-- Code block -->
                            <tr>
                                <td class="px" style="padding:18px 24px;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate; border-spacing:0; background:#F4F7FF; border:1px solid #D8E2FF; border-radius:14px;">
                                        <tr>
                                            <td style="padding:18px 16px;">
                                                <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:12px; letter-spacing:0.6px; color:#52608A; font-weight:700; text-transform:uppercase;">
                                                    Confirmation code
                                                </div>
                                                <div class="code" style="margin-top:10px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono','Courier New', monospace; font-size:32px; letter-spacing:4px; color:#0B1022; font-weight:800;">
                                                    {{$data['confirmation_code']}}
                                                </div>
                                                <div style="margin-top:10px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:12px; color:#52608A;">
                                                    Expires in <strong style="color:#0B1022;">24 hours</strong>.
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>



                            <!-- Divider -->
                            <tr>
                                <td class="px" style="padding:14px 24px 0 24px;">
                                    <div style="height:1px; background:#E7ECFA;"></div>
                                </td>
                            </tr>

                            <!-- Security note -->
                            <tr>
                                <td class="px" style="padding:14px 24px 26px 24px;">
                                    <p style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:13px; line-height:1.6; color:#394056;">
                                        If you did not request this, ignore this email. Your account stays safe unless someone provides the code.
                                    </p>
                                    <p style="margin:10px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:12px; line-height:1.6; color:#6A7188;">
                                        For support, contact <span style="color:#0B1022; font-weight:700;">info@stellarsecurity.com</span>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center" style="padding:16px 8px 0 8px;">
                        <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:12px; color:#7C89B2; line-height:1.6;">
                            © Stellar Security. Built for privacy, not vibes.
                        </div>
                        <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:11px; color:#5E6A93; line-height:1.z=er6014; margin-top:6px;">
                            This is an automated message. Please do not reply.
                        </div>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
