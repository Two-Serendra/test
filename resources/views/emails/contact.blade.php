<h2>Inquiry</h2>
<p><strong>Name:</strong> {{ $data['name'] }}</p>
<p><strong>Email:</strong> {{ $data['email'] }}</p>
<p><strong>Mobile:</strong> {{ $data['mobile'] ?? 'N/A' }}</p>
<p><strong>Subject:</strong> {{ $data['subject'] }}</p>
<p><strong>Message:</strong></p>
<p>{{ $data['inquiry'] }}</p>