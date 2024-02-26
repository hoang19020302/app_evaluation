<table>
    <thead>
        <tr>
            <th>Index</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($emails as $index => $email)
            <tr>
                <td>{{ $index + 1 }}</td> <!-- Thêm 1 để bắt đầu từ 1 thay vì 0 -->
                <td>{{ $email }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
