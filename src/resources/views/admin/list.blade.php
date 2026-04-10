@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="page-index">{{ $currentDate->isoFormat('YYYY年M月D日') }}の勤怠</h1>
        <div class="list-top">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDay] ) }}" class="list-top__prev link">前日</a>
            <p class="list-top__current">{{$currentDate->format('Y/m/d')}}</p>
            <a href="{{ route('admin.attendance.list', ['date' => $nextDay] ) }}" class="list-top__next link">翌日</a>
        </div>

        <div class="list">
            <table>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ $attendance ? $attendance->check_in_time_formatted : null }}</td>
                        <td>{{ $attendance ? $attendance->check_out_time_formatted : null }}</td>
                        <td>{{ $attendance?->totalRestTime() }}</td>
                        <td>{{ $attendance?->workTime() }}</td>
                        <td class="list-detail">
                            @if($attendance)
                                <a href="/admin/attendance/{{$attendance->id}}" class="link">詳細</a>
                            @else
                                詳細
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection