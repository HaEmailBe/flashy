<tr>
    {{-- <th scope="row">{{ $link->firstItem() + $index }}</th> --}}
    <th scope="row">{{ $links->firstItem() + $index }}</th>
    <td>{{ $link->id }}</td>
    <td class="text-wrap" style="max-width: 500px;">{{ $link->slug }}</td>
    <td class="text-center">{{ $link->hits_count }}</td>
    <td class="text-wrap" style="max-width: 500px;">{{ $link->target_url }}</td>
    <td>
        <span class="badge rounded-pill bg-{{ $link->is_active ? 'primary' : 'secondary' }}">
            {{ $link->is_active ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td>{{ $link->created_at }}</td>
    <td>{{ $link->updated_at }}</td>
    <td width="150">
        <a href="{{ route('links.show', $link->id) }}" class="btn btn-sm btn-circle btn-outline-info" title="Show"><i
                class="fa fa-eye"></i></a>
        <a href="form.html" class="btn btn-sm btn-circle btn-outline-secondary" title="Edit"><i
                class="fa fa-edit"></i></a>
        <a href="#" class="btn btn-sm btn-circle btn-outline-danger" title="Delete"
            onclick="confirm('Are you sure?')"><i class="fa fa-times"></i></a>
    </td>
</tr>
