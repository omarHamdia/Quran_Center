<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $recorded_by
 * @property \Illuminate\Support\Carbon $date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $check_in_time
 * @property string|null $excuse_reason
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \App\Models\Teacher $recordedBy
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereExcuseReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUpdatedAt($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $teacher_id
 * @property string $plan_type
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property int $from_surah_id
 * @property int $from_ayah
 * @property int $to_surah_id
 * @property int|null $from_page
 * @property int|null $to_page
 * @property int $to_ayah
 * @property string $status
 * @property string $plan_status
 * @property numeric $progress_percentage
 * @property int $total_ayahs
 * @property int $completed_ayahs
 * @property int $total_pages
 * @property int $completed_pages
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Surah $fromSurah
 * @property-read string $ayahs_progress
 * @property-read string $pages_progress
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MemorizationRecord> $memorizationRecords
 * @property-read int|null $memorization_records_count
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Teacher $teacher
 * @property-read \App\Models\Surah $toSurah
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereCompletedAyahs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereCompletedPages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereFromAyah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereFromPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereFromSurahId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan wherePlanStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan wherePlanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereProgressPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereToAyah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereToPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereToSurahId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereTotalAyahs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereTotalPages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationPlan whereUpdatedAt($value)
 */
	class MemorizationPlan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $teacher_id
 * @property int $surah_id
 * @property int|null $to_surah_id
 * @property int|null $memorization_plan_id
 * @property int $from_ayah
 * @property int $to_ayah
 * @property int|null $from_page
 * @property int|null $to_page
 * @property int $ayahs_count
 * @property \Illuminate\Support\Carbon $session_date
 * @property \Illuminate\Support\Carbon|null $session_time
 * @property int|null $duration_minutes
 * @property string $session_type
 * @property string $evaluation
 * @property int|null $score
 * @property int $mistakes_count
 * @property string|null $teacher_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $status
 * @property-read \App\Models\MemorizationPlan|null $memorizationPlan
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Surah $surah
 * @property-read \App\Models\Teacher $teacher
 * @property-read \App\Models\Surah|null $toSurah
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereAyahsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereEvaluation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereFromAyah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereFromPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereMemorizationPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereMistakesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereSessionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereSessionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereSessionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereSurahId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereTeacherNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereToAyah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereToPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereToSurahId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemorizationRecord withoutTrashed()
 */
	class MemorizationRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $surah_id
 * @property int $ayah_number
 * @property int $page_number
 * @property int|null $juz_number
 * @property int|null $hizb_number
 * @property int|null $rub_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Surah $surah
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah whereAyahNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah whereHizbNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah whereJuzNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah wherePageNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah whereRubNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah whereSurahId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuranAyah whereUpdatedAt($value)
 */
	class QuranAyah extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $teacher_id
 * @property string|null $guardian_name
 * @property string|null $guardian_phone
 * @property \Illuminate\Support\Carbon|null $enrollment_date
 * @property string $current_level
 * @property int $memorized_juz
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read string $current_level_label
 * @property-read string $name
 * @property-read string $phone
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MemorizationPlan> $memorizationPlans
 * @property-read int|null $memorization_plans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MemorizationRecord> $memorizationRecords
 * @property-read int|null $memorization_records_count
 * @property-read \App\Models\Teacher|null $teacher
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student forTeacher($teacherId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCurrentLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEnrollmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereGuardianName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereGuardianPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereMemorizedJuz($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUserId($value)
 */
	class Student extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $number
 * @property string $name_arabic
 * @property string $name_english
 * @property int $total_ayahs
 * @property string $revelation_type
 * @property int $page_start
 * @property int $page_end
 * @property int $juz_start
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $display_name
 * @property-read int $pages_count
 * @property-read string $revelation_type_label
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereJuzStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereNameArabic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereNameEnglish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah wherePageEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah wherePageStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereRevelationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereTotalAyahs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Surah whereUpdatedAt($value)
 */
	class Surah extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $specialty
 * @property string|null $qualification
 * @property string|null $ijazah_details
 * @property \Illuminate\Support\Carbon|null $hire_date
 * @property int $max_students
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read int $available_slots
 * @property-read string $name
 * @property-read string $phone
 * @property-read int|null $students_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MemorizationPlan> $memorizationPlans
 * @property-read int|null $memorization_plans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MemorizationRecord> $memorizationRecords
 * @property-read int|null $memorization_records_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereIjazahDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereMaxStudents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereQualification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereSpecialty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereUserId($value)
 */
	class Teacher extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string $password
 * @property \App\Enums\UserRole $role
 * @property string $gender
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string|null $address
 * @property string|null $avatar
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon|null $phone_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $age
 * @property-read string $gender_label
 * @property-read string $role_label
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Student|null $studentProfile
 * @property-read \App\Models\Teacher|null $teacherProfile
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User admins()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role(\App\Enums\UserRole $role)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User students()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User teachers()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

