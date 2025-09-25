@foreach ($residences as $residence)
<tr>
    <td>{{ $residence->section }}</td>
    <td>
        @php $type = strtolower($residence->resident_type); @endphp
        @if ($type === 'owner')
        <span class="badge bg-success badge-forge">OWNER</span>
        @elseif ($type === 'tenant')
        <span class="badge bg-danger badge-forge">TENANT</span>
        @else
        <span class="badge bg-secondary badge-forge">{{ $residence->resident_type }}</span>
        @endif
    </td>
    <td>{{ $residence->unit_no }}</td>
    <td>
        @php $status = strtoupper($residence->status ?? 'N/A'); @endphp
        @if ($status === 'PENDING')
        <span class="badge bg-warning badge-forge">{{ $status }}</span>
        @elseif ($status === 'ACTIVE')
        <span class="badge bg-success badge-forge">{{ $status }}</span>
        @elseif ($status === 'DENIED')
        <span class="badge bg-danger badge-forge">{{ $status }}</span>
        @else
        <span class="badge badge-secondary">{{ $status }}</span>
        @endif
    </td>
</tr>
@endforeach