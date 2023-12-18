<?php

namespace App\Http\Resources\Community;

use App\Models\Community\PollResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // $options = json_decode($this->options, true); // Decode the options as an associative array

        $data = [
            'id' => $this->id,
            'question' => $this->question,
            'options' => $this->options,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at,
            'ends_on' => $this->ends_on_diff,
        ];

        // Check if the user has submitted a response
        if ($response = $this->responses->where('submitted_by', auth()->id())->first()) {
            $data['selected_option'] = $response->answer;
        }

        // Calculate option statistics
        $optionStatistics = $this->calculateOptionStatistics($this->options);

        $data['option_statistics'] = $optionStatistics;

        return $data;
    }

    private function calculateOptionStatistics($options)
    {
        $optionStatistics = [];

        // Initialize option counters to zero
        foreach ($options as $key => $value) {
            $optionStatistics[$key] = 0;
        }

        $pollResponses = PollResponse::where('poll_id', $this->id)->get();

        // Count how many users selected each option
        foreach ($pollResponses as $response) {
            if (isset($optionStatistics[$response->answer])) {
                $optionStatistics[$response->answer]++;
            }
        }

        return $optionStatistics;
    }
}
