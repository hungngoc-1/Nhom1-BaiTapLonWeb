<?php
$zip = new ZipArchive();
$docxPath = 'C:/Users/admin/.gemini/antigravity/brain/4a1080ef-6292-430f-ae9a-0f140ab0131a/.user_uploaded/media_1793888240578_3e7a4861.docx';
if ($zip->open($docxPath) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    $text = preg_replace('/<w:p[^>]*>/', "\n", $xml);
    $text = strip_tags($text);
    $lines = explode("\n", $text);
    $output = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (!empty($trimmed)) {
            $output[] = $trimmed;
        }
    }
    file_put_contents(__DIR__ . '/docx_text.txt', implode("\n", $output));
    echo "Total lines: " . count($output) . "\n";
    echo "First 50 lines:\n" . implode("\n", array_slice($output, 0, 50)) . "\n";
} else {
    echo "Failed to open docx\n";
}
