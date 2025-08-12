<?php

namespace App\Services;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Support\Facades\Storage;

class VisionService
{
    protected $client;

    public function __construct()
    {
        $this->client = new ImageAnnotatorClient([
            'credentials' => config('services.google_vision.credentials'),
        ]);
    }

    public function detectLabels($imagePath)
    {
        $absolutePath = storage_path('app/' . ltrim($imagePath, '/'));

        if (!file_exists($absolutePath)) {
            return ['Error: Image not found.'];
        }

        $imageData = file_get_contents($absolutePath);
        $response = $this->client->labelDetection($imageData);
        $labels = $response->getLabelAnnotations();

        $tags = [];
        if ($labels) {
            foreach ($labels as $label) {
                $tags[] = $label->getDescription();
            }
        }

        return $tags;
    }
}
