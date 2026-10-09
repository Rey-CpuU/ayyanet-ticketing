<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Update</title>
</head>
<body style="margin: 0; padding: 0; background: #0f172a; color: #e2e8f0; font-family: Arial, sans-serif;">
    <div style="max-width: 600px; margin: 40px auto; padding: 24px;">
        <div style="background: #111827; border: 1px solid #2b3448; border-radius: 16px; padding: 24px;">
            <h2 style="margin: 0 0 16px; font-size: 1.5rem;">Ticket {{ $ticket->ticket_number }}</h2>
            <p style="margin: 0 0 12px; color: #94a3b8;">{{ $action }}</p>

            <div style="background: #1f2937; border: 1px solid #374151; border-radius: 12px; padding: 16px; margin-top: 16px;">
                <div style="font-size: 14px; color: #94a3b8; margin-bottom: 8px;">Judul</div>
                <div style="font-size: 18px; font-weight: 700;">{{ $ticket->title }}</div>

                <div style="font-size: 14px; color: #94a3b8; margin-top: 16px; margin-bottom: 8px;">Status</div>
                <div style="display: inline-block; padding: 6px 12px; border-radius: 999px; background: rgba(96,165,250,0.14); color: #93c5fd; font-size: 12px; font-weight: 700;">
                    {{ $ticket->status }}
                </div>
            </div>

            <a href="{{ url('/tickets/' . $ticket->id) }}" style="display: inline-block; margin-top: 20px; padding: 12px 18px; border-radius: 12px; background: #8b5cf6; color: white; text-decoration: none; font-weight: 700;">
                Lihat Ticket
            </a>
        </div>
    </div>
</body>
</html>
