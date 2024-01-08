<table class="table table-bordered table-striped datatable" id="dataTable" width="100%" cellspacing="0"
    data-url="banners_data">


    <tr>
        <th id="user_name" data-orderable="false" data-searchable="false">
            User Name</th>

        <th id="email" data-orderable="false" data-searchable="false">
            Email</th>

        <th id="start_date" data-orderable="false" data-searchable="false">
            Start Date</th>

        <th id="end_date" data-orderable="false" data-searchable="false">
            End Date</th>

    </tr>

    @foreach ($data->data as $column)
        <tr>
            <td>{{ $column->user_name }}</td>
            <td>{{ $column->email }}</td>
            <td>{{ $column->date }}</td>
            <td>{{ $column->start_date }}</td>
            <td>{{ $column->end_date }}</td>
        </tr>
    @endforeach
</table>
