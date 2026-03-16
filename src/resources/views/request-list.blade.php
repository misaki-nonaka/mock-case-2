@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="page-index">申請一覧</h1>
        <div class="tab-list">
            <ul class="tab-list__inner">
                <li class="tab-list__item"><a href="/stamp_correction_request/list?page=waiting" class="tab-list__link link {{ $activeTab === 'waiting' ? 'active' : '' }}">承認待ち</a></li>
                <li class="tab-list__item"><a href="/stamp_correction_request/list?page=complete" class="tab-list__link link {{ $activeTab === 'complete' ? 'active' : '' }}">承認済み</a></li>
            </ul>
        </div>

        <div class="list">
            <table>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
                @foreach($correctionRequests as $correctionRequest)
                    <tr>
                        <td>{{ $correctionRequest->status }}</td>
                        <td>{{ $correctionRequest->user->name }}</td>
                        <td>{{ $correctionRequest->attendance->work_date }}</td>
                        <td>{{ $correctionRequest->remark }}</td>
                        <td>{{ $correctionRequest->request_date }}</td>
                        <td class="list-detail">
                            <a href="/attendance/detail/{{$correctionRequest->attendance_id}}" class="link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection