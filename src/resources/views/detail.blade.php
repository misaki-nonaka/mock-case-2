@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@php
    $date = $requestAvailable ? $attendance->work_date : $attendance->attendance->work_date;
    $attendanceTime = $requestAvailable ? $attendance : $attendance->attendanceCorrection;
    $restTimes = $requestAvailable ? $attendance->rests : $attendance->restCorrections;
@endphp

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="page-index">勤怠詳細</h1>
        <div class="attendance-detail">
            <form action="/attendance/request" method="post">
            @csrf
                @if($requestAvailable ==true)
                    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                @endif
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td class="detail-table__date">
                            <span>{{ \Carbon\Carbon::parse($date)->isoFormat('YYYY年') }}</span>
                            <span>{{ \Carbon\Carbon::parse($date)->isoFormat('MM月DD日') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="detail-table__time-row">
                                    <input type="time" name="check_in_time" value="{{ old('check_in_time', $attendanceTime->check_in_time_formatted )}}" {{$requestAvailable == false ? 'readonly' : ''}}>
                                    <span class="wave">～</span>
                                    <input type="time" name="check_out_time" value="{{ old('check_out_time', $attendanceTime->check_out_time_formatted) }}" {{$requestAvailable == false ? 'readonly' : ''}}>
                            </div>
                            <div class="form__error">
                                @error('check_in_time')
                                    {{ $message }}
                                @enderror
                                @error('check_out_time')
                                    {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @foreach($restTimes as $rest)
                        <tr>
                            <th>{{ $loop->first ? '休憩' : '休憩'.$loop->iteration }}</th>
                            <td>
                                <div class="detail-table__time-row">
                                    <input type="time" name="rests[{{ $rest->id }}][rest_start_time]" value="{{ old("rests.$rest->id.rest_start_time", $rest->rest_start_formatted) }}" {{ $requestAvailable == false ? 'readonly' : ''}}>
                                    <span class="wave">～</span>
                                    <input type="time" name="rests[{{ $rest->id }}][rest_end_time]" value="{{ old("rests.$rest->id.rest_end_time", $rest->rest_end_formatted) }}" {{ $requestAvailable==false ? "readonly" : ""}}>
                                </div>
                                <div class="form__error">
                                    @error("rests.$rest->id.rest_start_time")
                                        {{ $message }}
                                    @enderror
                                    @error("rests.$rest->id.rest_end_time")
                                        {{ $message }}
                                    @enderror
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($requestAvailable == true)
                        <tr>
                            <th>休憩{{ $attendance->rests->count() + 1 }}</th>
                            <td>
                                <div class="detail-table__time-row">
                                    <input type="text" name="rests[new][rest_start_time]">
                                    <span class="wave">～</span>
                                    <input type="text" name="rests[new][rest_end_time]">
                                </div>
                                <div class="form__error">
                                    @error("rests.new.rest_start_time")
                                        {{ $message }}
                                    @enderror
                                    @error("rests.new.rest_end_time")
                                        {{ $message }}
                                    @enderror
                                </div>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <th>備考</th>
                        <td><textarea name="remark" {{ $requestAvailable==false ? "readonly" : ""}}>{{ old('remark'), $requestAvailable == false ? $attendance->remark : '' }}</textarea>
                        <div class="form__error">
                            @error("remark")
                                {{ $message }}
                            @enderror
                        </div>
                        </td>
                    </tr>
                </table>
                <div class="submit-button">
                    @if($requestAvailable == true)
                        <button type="submit">修正</button>
                    @else
                        <p class="request-message">*承認待ちのため修正はできません。</p>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection