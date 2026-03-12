@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="page-index">勤怠一覧</h1>
        <div class="list-top">
            <a href="{{ route('list', ['date' => $prevMonth] ) }}" class="prev-month link">前月</a>
            <p class="current-month">{{$currentDate->format('Y/m')}}</p>
            <a href="{{ route('list', ['date' => $nextMonth] ) }}" class="next-month link">翌月</a>
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
                        <td>{{$date->isoFormat('MM月DD日(ddd)')}}</td>
                        <td>{{ $attendance?->check_in_time }}</td>
                        <td>{{ $attendance?->check_out_time }}</td>
                        <td>{{ $attendance?->totalRestTime() }}</td>
                        <td>{{ $attendance?->workTime() }}</td>
                        <td class="list-detail">
                            @if($attendance)
                                <a href="/attendance/detail/{{$attendance->id}}" class="link">詳細</a>
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