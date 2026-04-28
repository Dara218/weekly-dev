<?php

namespace App\Helpers;

class FormatFilePathHelper
{
    /**
     * Format the student document file.
     *
     * @param int $studentId The id of the stundent
     * @param string $fileName The name of the file
     * @param int $count The number of same existing file in the database
     *
     * @return string
     */
    public static function formatStudentFilePath(
        int $studentId,
        string $fileName,
        int $count = 0,
    ): string {
        $studentDocumentPath = config('filesystems.paths.student_documents');

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);

        $finalName = $count > 0
            ? "{$nameOnly}({$count}).{$extension}"
            : $fileName;

        return $studentDocumentPath . "$studentId/" . $finalName;
    }
}
