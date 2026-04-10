@extends('layouts.app')

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="page-index">スタッフ一覧</h1>

        <div class="list">
            <table>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td class="list-detail">
                            <a href="/admin/attendance/staff/{{$user->id}}" class="link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>

@endsection