<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

if (!function_exists('format_number_short')) {
    /**
     * Formats a number into a short human-readable format (k, M, B).
     *
     * @param int|float $number The number to format.
     * @param int $precision The number of decimal places.
     * @return string The formatted number.
     */
    function format_number_short($number, int $precision = 1): string
    {
        if ($number < 1000) {
            // Less than 1000, just format normally
            return number_format($number);
        }

        $suffixes = ['', 'k', 'M', 'B', 'T']; // Add more suffixes if needed (Trillion, Quadrillion, etc.)
        $power = floor(log10($number) / 3); // Calculate the power of 1000

        // Ensure power doesn't exceed available suffixes
        $power = min($power, count($suffixes) - 1);

        $divisor = pow(1000, $power);
        $formattedNumber = $number / $divisor;

        // Format the number with specified precision
        $formatted = number_format($formattedNumber, $precision);

        // Remove trailing '.0' if precision is 1 and it ends with .0
        if ($precision === 1 && str_ends_with($formatted, '.0')) {
            $formatted = substr($formatted, 0, -2);
        }
        // Add similar checks if precision > 1 is needed, e.g., remove '.00'

        return $formatted . $suffixes[$power];
    }

    function time_elapsed_string($dateTime, bool $short = false, ?string $locale = null): string
    {
        if (is_null($dateTime)) {
            return ''; // Return empty if no date provided
        }

        // Ensure it's a Carbon instance
        try {
            $carbonDate = ($dateTime instanceof Carbon) ? $dateTime : Carbon::parse($dateTime);
        } catch (\Exception $e) {
            // Handle invalid date format if necessary
            return ''; // Or return a default error message
        }

        // Set locale if provided, otherwise use app locale
        $currentLocale = $locale ?? App::getLocale();

        // Use diffForHumans with appropriate options
        return $carbonDate->locale($currentLocale)->diffForHumans([
            'short' => $short, // Use 'short' => true for formats like 1w, 2d
            'syntax' => Carbon::DIFF_RELATIVE_TO_NOW, // Ensure it says "ago" or "from now"
            'options' => Carbon::NO_ZERO_DIFF, // Avoid "0 seconds ago"
        ]);
    }

    function cleanDescription($content, $limit = 150)
    {
        $text = strip_tags($content);

        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        $text = preg_replace('/\s+/', ' ', $text);

        $text = trim($text);

        return Str::limit($text, $limit);
    }
}
