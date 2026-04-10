@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="page-index">勤怠詳細</h1>
        <div class="attendance-detail">
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>{{ $correction->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td class="detail-table__date">
                        <span>{{ \Carbon\Carbon::parse($correction->attendance->work_date)->isoFormat('YYYY年') }}</span>
                        <span>{{ \Carbon\Carbon::parse($correction->attendance->work_date)->Format('n月j日') }}</span>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="detail-table__time-row">
                                <p>{{ $correction->attendanceCorrection->check_in_time_formatted }}</p>
                                <span class="wave">～</span>
                                <p>{{ $correction->attendanceCorrection->check_out_time_formatted }}</p>
                        </div>
                        <div class="form__error">
                        </div>
                    </td>
                </tr>
                @foreach($correction->restCorrections as $rest)
                    <tr>
                        <th>{{ $loop->first ? '休憩' : '休憩'.$loop->iteration }}</th>
                        <td>
                            <div class="detail-table__time-row">
                                <p>{{ $rest->rest_start_formatted }}</p>
                                <span class="wave">～</span>
                                <p>{{ $rest->rest_end_formatted }}</p>
                            </div>
                            <div class="form__error">
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th>休憩{{ $correction->restCorrections->count() + 1 }}</th>
                    <td>
                        <div class="detail-table__time-row">
                            <p>　</p>
                        </div>
                        <div class="form__error">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <p>{{ old('remark', $correction->remark )}}</p>
                        <div class="form__error">
                        </div>
                    </td>
                </tr>
            </table>
            <form action="/stamp_correction_request/approve/{{ $correction->id }}" method="post">
            @csrf
                <div class="submit-button">
                    @if($correction->status == "承認待ち")
                        <button type="submit">承認</button>
                    @else
                        <button type="submit" disabled>承認済み</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection