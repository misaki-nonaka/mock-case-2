@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="page-index">{{ $user->name }}さんの勤怠</h1>
        <div class="list-top">
            <a href="{{ route('admin.individual', ['id' => $user->id, 'date' => $prevMonth] ) }}" class="list-top__prev link">前月</a>
            <p class="list-top__current">{{$currentDate->format('Y/m')}}</p>
            <a href="{{ route('admin.individual', ['id' => $user->id, 'date' => $nextMonth] ) }}" class="list-top__next link">翌月</a>
        </div>

        <div class="list">
            <table>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                @foreach($period as $date)
                    @php
                        $attendance = $attendances[$date->format('Y-m-d')] ?? null;
                    @endphp

                    <tr>
                        <td>{{ $date->isoFormat('MM/DD(ddd)') }}</td>
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
        <div class="export-form">
            <form action="/admin/export/{{$user->id}}/{{$currentDate}}" method="post">
            @csrf
                <div class="submit-button">
                    <button type="submit">CSV出力</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection