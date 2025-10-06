<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'new_clock_in' => ['required', 'date_format:H:i', 'before:new_clock_out'],
            'new_clock_out' => ['required', 'date_format:H:i'],
            'notes' => ['required', 'string', 'max:255'],

            'new_start_break' => ['array'],
            'new_end_break' => ['array'],

            'new_start_break.*' => ['nullable', 'date_format:H:i'],
            'new_end_break.*' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages()
    {
        return [
            'new_clock_in.required' => '出勤時間を入力してください',
            'new_clock_in.date_format' => '00:00形式で入力してください',
            'new_clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',

            'new_clock_out.required' => '退勤時間を入力してください',
            'new_clock_out.date_format' => '00:00形式で入力してください',

            'notes.required' => '備考を記入してください',
            'notes.string' => '文字列で記入してください',
            'notes.max' => '備考は255文字以内で記入してください',

            'new_start_break.*.date_format' => '00:00形式で入力してください',

            'new_end_break.*.date_format' => '00:00形式で入力してください',

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->new_clock_in || !$this->new_clock_out) return;

            $clockIn = Carbon::createFromFormat('H:i', $this->new_clock_in);
            $clockOut = Carbon::createFromFormat('H:i', $this->new_clock_out);

            $starts = $this->input('new_start_break', []);
            $ends   = $this->input('new_end_break', []);

            foreach ($starts as $i => $start) {
                $end = $ends[$i] ?? null;

                if (blank($start) && blank($end)) {
                    continue;
                }

                // 🟠 片方だけ空ならエラー
                if (blank($start) xor blank($end)) {
                    $validator->errors()->add("new_start_break.$i", '休憩の開始と終了は両方入力してください');
                    continue;
                }

                // 🟢 Carbon変換は空でない場合のみ
                try {
                    $startTime = Carbon::createFromFormat('H:i', $start);
                    $endTime   = Carbon::createFromFormat('H:i', $end);
                } catch (\Exception $e) {
                    continue; // 変換失敗はスキップ
                }

                // ① 休憩開始 > 終了（逆転している場合）
                if ($startTime && $endTime && $startTime->gt($endTime)) {
                    $validator->errors()->add("new_end_break.$i", '休憩終了時間が開始時間より前です');
                }

                // ② 休憩開始時間が出勤時間より前 または 退勤時間より後の場合
                if ($startTime && ($startTime->lt($clockIn) || $startTime->gt($clockOut))) {
                    $validator->errors()->add("new_start_break.$i", '休憩時間が不適切な値です');
                }

                // ③ 休憩終了時間が退勤時間より後の場合
                if ($endTime && $endTime->gt($clockOut)) {
                    $validator->errors()->add("new_end_break.$i", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
