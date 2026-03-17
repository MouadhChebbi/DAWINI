<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de réinitialisation</title>
    <!-- Safe, inline styles only – compatible with most email clients -->
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    <!-- MAIN CONTAINER TABLE: 100% width, centered background -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" bgcolor="#f4f6f9" style="background-color:#f4f6f9; width:100%; max-width:100%;">
        <tr>
            <td align="center" valign="top" style="padding:30px 15px; background-color:#f4f6f9;">
                <!-- INNER CARD TABLE: white card, 600px max width, subtle radius -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" bgcolor="#FFFFFF" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:16px; box-shadow:0 8px 20px rgba(0,0,0,0.05); border:1px solid #e9ecf0;">
                    <tr>
                        <td align="center" valign="top" style="padding:40px 35px; background-color:#ffffff; border-radius:16px;">
                            <!-- HEADER / TITLE -->
                            <h1 style="font-size:28px; font-weight:300; color:#1a2b3c; margin:0 0 15px 0; letter-spacing:-0.5px; line-height:1.2; border-bottom:2px solid #e6edf4; padding-bottom:20px; width:100%; text-align:left;">
                                🔐 Demande de réinitialisation
                            </h1>
                            
                            <!-- GREETING / INTRO -->
                            <p style="font-size:16px; line-height:1.6; color:#2f3e4e; margin:0 0 20px 0; text-align:left;">
                                Bonjour, nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.
                            </p>
                            
                            <!-- ORIGINAL SENTENCE: "Votre code de réinitialisation est :" with inline code inside a beautiful box (but we keep the meaning) -->
                            <p style="font-size:16px; line-height:1.6; color:#2f3e4e; margin:0 0 10px 0; text-align:left;">
                                <strong style="color:#1a2b3c;">Votre code de réinitialisation est :</strong>
                            </p>
                            
                            <!-- CODE BOX : large, monospace, gradient background, subtle border, strong presence -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:15px 0 25px 0;">
                                <tr>
                                    <td align="center" bgcolor="#eef3fa" style="background-color:#eef3fa; border-radius:14px; padding:22px 15px; border:1px solid #cbd7e6;">
                                        <span style="font-family:'Courier New', 'SF Mono', monospace; font-size:42px; font-weight:700; color:#144e8c; letter-spacing:6px; line-height:1.2; display:inline-block; word-break:break-word;">
                                            {{ $code }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- EXPIRY NOTICE (original message) with subtle icon -->
                            <p style="font-size:15px; color:#5b6f82; margin:0 0 30px 0; padding:10px 18px; background-color:#f9fafc; border-radius:40px; display:inline-block; border:1px dashed #b8c9dd;">
                                ⏳ Ce code expirera dans <strong style="color:#1f4e8c;">10 minutes</strong>
                            </p>
                            
                            <!-- DECORATIVE DIVIDER -->
                            <div style="height:1px; background:linear-gradient(to right, #ffffff, #cbd7e6, #ffffff); margin:25px 0 20px 0;"></div>
                            
                            <!-- FOOTER NOTE (ignoring if not requested) – polite and secure -->
                            <p style="font-size:13px; color:#8a9bb0; line-height:1.5; margin:0; text-align:left;">
                                ⚙️ Si vous n’êtes pas à l’origine de cette demande, ignorez simplement cet email. Aucune modification n’a été effectuée sur votre compte.
                            </p>
                            
                            <!-- SMALL CONTACT / HELP TEXT -->
                            <p style="font-size:13px; color:#8a9bb0; line-height:1.5; margin:15px 0 0 0; text-align:left;">
                                📬 Pour toute assistance, contactez notre <a href="mailto:mouadhnwa3@gmail.com" style="color:#2663a6; text-decoration:none; border-bottom:1px dotted #2663a6;">support</a>.
                            </p>
                        </td>
                    </tr>
                </table>
                <!-- POSTSCRIPT: tiny note about expiry – already included above, but we keep as spacer -->
                <p style="font-size:12px; color:#b1c2d4; margin:15px 0 0 0; text-align:center;">
                    &copy; 2025 • Sécurité • Chiffré de bout en bout
                </p>
            </td>
        </tr>
    </table>

    <!-- optional fallback for Outlook: force background on body -->
    <div style="display:none;"> </div> <!-- hidden spacer -->
</body>
</html>