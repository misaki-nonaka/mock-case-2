<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'check_in_time' => ['required', 'date_format:H:i', 'before:check_out_time'],
            'check_out_time' => ['required', 'date_format:H:i', 'after:check_in_time'],
            'rests.*.rest_start_time' => ['nullable', 'date_format:H:i', 'after:check_in_time',
            'before:check_out_time'],
            'rests.*.rest_end_time' => ['nullable', 'date_format:H:i', 'after:rest_start_time',
            'before:check_out_time'],
            'remark' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'check_in_time.required' => '出勤時間を入力してください',
            'check_in_time.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'check_out_time.required' => '退勤時間を入力してください',
            'check_out_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'rests.*.rest_start_time.after' => '休憩時間が不適切な値です',
            'rests.*.rest_start_time.before' => '休憩時間が不適切な値です',
            'rests.*.rest_end_time.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'rests.*.rest_end_time.after' => '休憩時間が不適切な値です',
            'remark.required' => '備考を記入してください'

        ];
    }
}
