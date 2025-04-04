<!DOCTYPE html>
<html>
<head>
    <title>📩 Inquiry tentang {{ $contact->subject }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>📩 Inquiry tentang {{ $contact->subject }}</h2>
        
        <p>Halo <strong>Agridation Team</strong> 👋,</p>
        
        <p>Saya ingin mengajukan pertanyaan terkait <strong>{{ $contact->subject }}</strong>. Berikut adalah detail saya:</p>

        <p><strong>📧 Email:</strong> {{ $contact->email }}</p>
        <p><strong>📱 No HP:</strong> {{ $contact->phone }}</p>
        <p><strong>📝 Pesan:</strong></p>
        <p>{{ $contact->message }}</p>

        <p>Mohon informasinya lebih lanjut mengenai hal ini. Saya sangat menantikan balasan dari tim Agridation. Terima kasih! 🙌</p>

        <p><strong>Best regards,</strong><br>
        {{ $contact->name }}</p>
    </div>
</body>
</html>