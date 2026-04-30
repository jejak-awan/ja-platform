<?php

namespace Modules\School\Services\Lms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoodleService
{
    protected string $baseUrl;
    protected string $token;
    protected string $restFormat = 'json';

    public function __construct()
    {
        $this->baseUrl = config('school.lms.moodle.url', env('MOODLE_URL'));
        $this->token = config('school.lms.moodle.token', env('MOODLE_TOKEN'));
    }

    /**
     * Call Moodle Web Service Function
     */
    protected function call(string $function, array $params = [])
    {
        $url = "{$this->baseUrl}/webservice/rest/server.php";
        
        $query = array_merge([
            'wstoken' => $this->token,
            'wsfunction' => $function,
            'moodlewsrestformat' => $this->restFormat,
        ], $params);

        try {
            $response = Http::get($url, $query);
            
            if ($response->failed()) {
                Log::error("Moodle API Error: {$response->body()}");
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Moodle Connection Failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Get All Courses
     */
    public function getCourses()
    {
        return $this->call('core_course_get_courses');
    }

    /**
     * Get User Enrolled Courses
     */
    public function getUserCourses(int $moodleUserId)
    {
        return $this->call('core_enrol_get_users_courses', [
            'userid' => $moodleUserId
        ]);
    }

    /**
     * Get Course Contents
     */
    public function getCourseContents(int $courseId)
    {
        return $this->call('core_course_get_contents', [
            'courseid' => $courseId
        ]);
    }

    /**
     * Get User Grades for a Course
     */
    public function getCourseGrades(int $courseId, int $moodleUserId)
    {
        return $this->call('gradereport_user_get_grade_items', [
            'courseid' => $courseId,
            'userid' => $moodleUserId
        ]);
    }
}
