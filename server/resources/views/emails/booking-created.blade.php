<h2>Sikeres foglalás 💈</h2>

<p>Szia {{ $appointment->user->name }}!</p>

<p>Az időpontfoglalásod rögzítve lett a rendszerünkben.</p>

<h3>Foglalás részletei:</h3>

<ul>
    <li>
        <strong>Időpont:</strong>
        {{ \Carbon\Carbon::parse($appointment->appointmentDate)->format('Y. m. d.') }}
        {{ \Carbon\Carbon::parse($appointment->appointmentTime)->format('H:i') }}
    </li>
    <li><strong>Barber:</strong> {{ $appointment->barber->user->name }}</li>
</ul>

<h4>Szolgáltatások:</h4>
<ul>
    @foreach($appointment->services as $service)
        <li>{{ $service->name }}</li>
    @endforeach
</ul>

<p>Kérjük, érkezz pontosan! Várunk szeretettel ✂️</p>

<hr>
<p>Barber Shop csapata</p>