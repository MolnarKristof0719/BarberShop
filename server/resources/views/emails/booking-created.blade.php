<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* Modern inline-stílusok a levelezőrendszerek miatt */
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
        .card { max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #111111; color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; letter-spacing: 3px; text-transform: uppercase; }
        .content { padding: 30px; color: #333333; }
        .welcome { font-size: 20px; font-weight: 600; margin-bottom: 10px; }
        .details-grid { background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e9ecef; }
        .detail-item { margin-bottom: 15px; }
        .detail-label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 4px; }
        .detail-value { font-size: 16px; font-weight: 500; color: #111; }
        .service-tag { 
            display: inline-block; 
            background: #111; 
            color: #fff; 
            padding: 4px 12px; 
            border-radius: 15px; 
            font-size: 12px; 
            margin: 4px 4px 0 0; 
        }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 13px; }
        .accent { color: #c5a059; } /* Egy kis barber-arany szín */
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>BARBER <span class="accent">SHOP</span></h1>
        </div>
        
        <div class="content">
            <div class="welcome">Szia {{ $appointment->user->name }}!</div>
            <p>A foglalásod sikeresen rögzítettük. Hamarosan találkozunk!</p>

            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Időpont</div>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($appointment->appointmentDate)->format('Y. m. d.') }} 
                        <span class="accent">|</span> 
                        {{ \Carbon\Carbon::parse($appointment->appointmentTime)->format('H:i') }}
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Barber</div>
                    <div class="detail-value">{{ $appointment->barber->user->name }}</div>
                </div>

                <div class="detail-item" style="margin-bottom: 0;">
                    <div class="detail-label">Szolgáltatások</div>
                    <div>
                        @forelse($appointment->services as $service)
                            <span class="service-tag">{{ $service->service }}</span>
                        @empty
                            <span class="service-tag">Hajvágás</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <p style="font-size: 14px; line-height: 1.5; color: #666;">
                Kérjük, érkezz 5 perccel korábban. Ha közbejönne valami, kérlek jelezd nekünk időben!
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Barber Shop Team</p>
            <p style="color: #f0f2f5; font-size: 1px;">UID: {{ uniqid() }}</p>
        </div>
    </div>
</body>
</html>