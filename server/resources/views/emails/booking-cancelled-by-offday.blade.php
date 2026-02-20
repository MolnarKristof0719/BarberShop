<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
        .card { max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #111111; color: #ffffff; padding: 32px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; letter-spacing: 2px; text-transform: uppercase; }
        .content { padding: 28px; color: #333333; }
        .details { background: #f8f9fa; border-radius: 8px; padding: 18px; margin: 18px 0; border: 1px solid #e9ecef; }
        .label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 4px; }
        .value { font-size: 16px; font-weight: 500; color: #111; margin-bottom: 10px; }
        .footer { text-align: center; padding: 16px; color: #999; font-size: 13px; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>BARBER SHOP</h1>
    </div>

    <div class="content">
        <p>Szia {{ $appointment->user->name }}!</p>
        <p>
            A lefoglalt időpontodat le kellett mondanunk, mert a barber erre a napra szabadnapot rögzített.
        </p>

        <div class="details">
            <div class="label">Eredeti időpont</div>
            <div class="value">
                {{ \Carbon\Carbon::parse($appointment->appointmentDate)->format('Y. m. d.') }}
                | {{ \Carbon\Carbon::parse($appointment->appointmentTime)->format('H:i') }}
            </div>

            <div class="label">Barber</div>
            <div class="value">{{ $appointment->barber->user->name }}</div>
        </div>

        <p>Köszönjük a megértésedet. Kérd új időpont foglalását egy másik alkalomra.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Barber Shop Team</p>
    </div>
</div>
</body>
</html>