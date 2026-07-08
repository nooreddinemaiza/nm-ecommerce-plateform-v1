<?php
namespace Src\Helpers;

class ProductPageHelper {
    /**
     * Combine data by a specific key while preserving arrays and handling images
     */
    public static function combineDataByKey($data, $key)
    {
        if (empty($data) || !is_array($data)) {
            return [];
        }

        $combinedData = [];
        foreach ($data as $item) {
            if (!isset($item[$key])) {
                continue;
            }

            $uniqueKey = $item[$key];
            
            // Initialize if not exists
            if (!isset($combinedData[$uniqueKey])) {
                $combinedData[$uniqueKey] = $item;
                
                // Ensure images are in the correct format
                if (isset($item['images']) && !is_array($item['images'])) {
                    $combinedData[$uniqueKey]['images'] = self::formatImages($item['images']);
                }
                continue;
            }

            // Process each field
            foreach ($item as $field => $value) {
                if ($field === $key) {
                    continue;
                }

                // Special handling for images field
                if ($field === 'images') {
                    $combinedData[$uniqueKey]['images'] = self::mergeImages(
                        $combinedData[$uniqueKey]['images'] ?? '',
                        $value
                    );
                    continue;
                }

                // Handle categories field
                if ($field === 'categories' && !empty($value)) {
                    $combinedData[$uniqueKey]['categories'] = self::mergeCategories(
                        $combinedData[$uniqueKey]['categories'] ?? '',
                        $value
                    );
                    continue;
                }

                // Handle other fields
                if ($combinedData[$uniqueKey][$field] !== $value) {
                    if (is_array($combinedData[$uniqueKey][$field])) {
                        if (!in_array($value, $combinedData[$uniqueKey][$field])) {
                            $combinedData[$uniqueKey][$field][] = $value;
                        }
                    } else {
                        $combinedData[$uniqueKey][$field] = array_unique([
                            $combinedData[$uniqueKey][$field],
                            $value
                        ]);
                    }
                }
            }
        }

        return array_values($combinedData);
    }

    /**
     * Safely decode JSON data and handle special fields
     */
    public static function safeJsonDecode($data, $key)
    {
        if (!isset($data[$key])) {
            return $data;
        }

        // Handle empty data
        if (empty($data[$key])) {
            $data[$key] = [];
            return $data;
        }

        // Decode JSON string if necessary
        if (is_string($data[$key])) {
            $decoded = json_decode($data[$key], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data[$key] = $decoded;
            }
        }

        // Process array data
        if (is_array($data[$key])) {
            $data[$key] = self::processArrayData($data[$key]);
        }

        return $data;
    }

    /**
     * Format images string into standardized format
     */
    private static function formatImages($imagesString)
    {
        if (empty($imagesString)) {
            return [];
        }

        $images = [];
        $imgParts = explode(',', $imagesString);
        
        foreach ($imgParts as $img) {
            $parts = explode('|', $img);
            if (count($parts) === 2) {
                $images[] = [
                    'id' => $parts[0],
                    'path' => $parts[1]
                ];
            }
        }

        return $images;
    }

    /**
     * Merge two image strings/arrays
     */
    private static function mergeImages($existing, $new)
    {
        $existingImages = is_array($existing) ? $existing : self::formatImages($existing);
        $newImages = is_array($new) ? $new : self::formatImages($new);

        $merged = array_merge($existingImages, $newImages);
        $unique = [];

        foreach ($merged as $image) {
            $key = $image['id'] . '|' . $image['path'];
            $unique[$key] = $image;
        }

        return array_values($unique);
    }

    /**
     * Merge category strings
     */
    private static function mergeCategories($existing, $new)
    {
        $existingCats = is_array($existing) ? $existing : explode(',', $existing);
        $newCats = is_array($new) ? $new : explode(',', $new);

        return implode(', ', array_unique(array_map('trim', array_merge($existingCats, $newCats))));
    }

    /**
     * Process array data recursively
     */
    private static function processArrayData($array)
    {
        foreach ($array as $key => $value) {
            if (is_object($value)) {
                $array[$key] = json_decode(json_encode($value), true);
            } elseif (is_array($value)) {
                $array[$key] = self::processArrayData($value);
            } elseif (is_string($value)) {
                $array[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        return $array;
    }
}