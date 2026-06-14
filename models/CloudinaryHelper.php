<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */


class CloudinaryHelper
{
    private static function apiUrl(string $resource_type = 'auto'): string
    {
        return sprintf(
            'https://api.cloudinary.com/v1_1/%s/%s/upload',
            CLOUDINARY_CLOUD_NAME,
            $resource_type
        );
    }

    private static function signature(array $params = []): string
    {
        $to_sign = [];
        // On trie les paramètres alphabétiquement (requis par Cloudinary)
        ksort($params);
        
        foreach ($params as $key => $value) {
            if ($value !== null && $value !== '') {
                $to_sign[] = $key . '=' . $value;
            }
        }
        
        $string_to_sign = implode('&', $to_sign);
        return sha1($string_to_sign . CLOUDINARY_API_SECRET);
    }

    /**
     * Upload un fichier vers Cloudinary.
     */
    public static function upload(string $file_path, string $folder, string $public_id, string $resource_type = 'auto'): ?array
    {
        if (empty(CLOUDINARY_CLOUD_NAME) || empty(CLOUDINARY_API_KEY) || empty(CLOUDINARY_API_SECRET)) {
            return null;
        }

        $timestamp = time();
        $params = [
            'folder' => $folder,
            'public_id' => $public_id,
            'timestamp' => $timestamp,
        ];

        // On ne signe le resource_type que s'il est spécifié et différent de auto
        // car il n'est pas toujours requis dans la signature de l'upload simple
        
        $signature = self::signature($params);

        $post_fields = [
            'file' => new CURLFile($file_path),
            'api_key' => CLOUDINARY_API_KEY,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder,
            'public_id' => $public_id,
        ];

        // resource_type est passé en paramètre d'URL dans l'API Cloudinary
        $url = self::apiUrl($resource_type);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300,
        ]);


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        if (!isset($data['secure_url'])) {
            return null;
        }

        return [
            'url' => $data['secure_url'],
            'public_id' => $data['public_id'],
        ];
    }

    /**
     * Supprimer un fichier de Cloudinary.
     */
    public static function destroy(string $public_id, string $resource_type = 'image'): bool
    {
        if (empty(CLOUDINARY_API_KEY) || empty(CLOUDINARY_API_SECRET)) {
            return false;
        }

        $timestamp = time();
        $to_sign = "public_id={$public_id}&timestamp={$timestamp}";
        $signature = sha1($to_sign . CLOUDINARY_API_SECRET);

        $url = sprintf(
            'https://api.cloudinary.com/v1_1/%s/%s/destroy',
            CLOUDINARY_CLOUD_NAME,
            $resource_type
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'public_id' => $public_id,
                'api_key' => CLOUDINARY_API_KEY,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return false;

        $data = json_decode($response, true);
        return isset($data['result']) && $data['result'] === 'ok';
    }

    /**
     * Determiner le resource_type et le dossier Cloudinary selon le MIME.
     */
    public static function getConfigForMime(string $mime): array
    {
        if (in_array($mime, MIME_PDF)) {
            return ['resource_type' => 'raw', 'folder' => 'lms/lecons/pdfs'];
        }
        if (in_array($mime, MIME_VIDEO)) {
            return ['resource_type' => 'video', 'folder' => 'lms/lecons/videos'];
        }
        if (in_array($mime, MIME_IMAGE)) {
            return ['resource_type' => 'image', 'folder' => 'lms/cours/images'];
        }
        return ['resource_type' => 'raw', 'folder' => 'lms/uploads'];
    }
}
