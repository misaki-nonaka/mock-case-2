@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="content center">
    <div class="content__inner">
        <div class="status">
            <p>
                @if(!$attendance)
                    勤務外
                @else
                    {{$attendance->status}}
                @endif
            </p>
        </div>
        <div class="date">
            <p>{{ \Carbon\Carbon::now()->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
        </div>
        <div class="time">
            <p id="realtime-clock">
                {{ now()->format('H:i') }}
            </p>
            <script>
                function updateTime() {
                    const now = new Date();

                    const formattedTime =                     ('0' + now.getHours()).slice(-2) + ':' +
                        ('0' + now.getMinutes()).slice(-2);

                    document.getElementById('realtime-clock').innerText = formattedTime;
                }

                setInterval(updateTime, 1000);
            </script>
        </div>
        <div class="attendance-button">
            <form action="/attendance/register" method="post">
                @csrf
                <input type="hidden" name="attendance_id" value="{{optional($attendance)->id}}">
                @if(!$attendance)
                    <button type="submit" name='attendance_register' value="check-in" class="attendance-button__check">出勤</button>
                @elseif($attendance->status == "出勤中")
                    <button type="submit" name='attendance_register' value="check-out" class="attendance-button__check">退勤</button>
                    <button type="submit" name='attendance_register' value="rest-start" class="attendance-button__rest">休憩入</button>
                @elseif($attendance->status == "休憩中")
                    <button type="submit" name='attendance_register' value="rest-end" class="attendance-button__rest">休憩戻</button>
                @elseif($attendance->status == "退勤済")
                    <p class="check-out-message">お疲れ様でした。</p>
                @endif
            </form>
        </div>
    </div>
</div>

@endsection