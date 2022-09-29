<table width="100%">
    <tbody>
        <tr>
            <td colspan="7">Monthly Sales Report ( {{ $strMonth }} ) </td>
        </tr>
        <tr>
            <td>Id</td>
            <td>Sale Agent</td>
            <td>Manager Name</td>
            <td>Rpt Category</td>
            <td>Month</td>
            <td>Weight</td>
            <td>Amt</td>
        </tr>
        @forelse($results as $result)
        <tr>
            <td>{{$result->id}}</td>
            <td>{{$result->salesAgent}}</td>
            <td>{{$result->managerName}}</td>
            <td>{{$result->rptCategory}}</td>
            <td>{{$result->strMonth}}</td>
            <td>{{$result->weight}}</td>
            <td>{{$result->amt}}</td>
        </tr>
        @empty
        <tr><td colspan="7">There are no items.</td></tr>
        @endforelse
    </tbody>
</table>

