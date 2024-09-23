<?php
namespace App\State\Processor\School\Study\Timetable;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\School\Study\TimeTable\TimeTableModelDay;
use App\Entity\School\Study\TimeTable\TimeTableModelDayCell;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SpecialityRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GenerateTimeTableModelProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;
    private ClassProgramRepository $classProgramRepo;
    private SchoolClassRepository $schoolClassRepository;
    private SpecialityRepository $specialityRepository;
    private LevelRepository $levelRepository;


    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                Request $request,
                                EntityManagerInterface $manager, SchoolClassRepository $schoolClassRepository, SpecialityRepository $specialityRepository,
                                LevelRepository $levelRepository,
                                private readonly TimeTableModelDayRepository $timeTableModelDayRepo,
                                ClassProgramRepository $classProgramRepo,
                                private readonly TeacherCourseRegistrationRepository $teacherCourseRegistrationRepo) {
        $this->req = $request;
        $this->manager = $manager;
        $this->classProgramRepo = $classProgramRepo;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->specialityRepository = $specialityRepository;
        $this->levelRepository = $levelRepository;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $modelData = json_decode($this->req->getContent(), true);

        $classPrograms = $this->classProgramRepo->findAll();
        $matchingClassPrograms = [];

        $speciality = $this->specialityRepository->find($this->getIdFromApiResourceId($modelData['speciality'] ?? ''));
        $level = $this->levelRepository->find($this->getIdFromApiResourceId($modelData['level'] ?? ''));
        $class = $this->schoolClassRepository->find($this->getIdFromApiResourceId($modelData['class'] ?? ''));

        foreach ($classPrograms as $classProgram) {
            if ($classProgram->getClass()->getSpeciality()){
                if ($classProgram->getClass()->getSpeciality() === $speciality){
                    $matchingClassPrograms[] = $classProgram;
                }
            }

            if ($classProgram->getClass()->getLevel()){
                if ($classProgram->getClass()->getLevel() === $level){
                    $matchingClassPrograms[] = $classProgram;
                }
            }

            if ($classProgram->getClass()){
                if ($classProgram->getClass() === $class){
                    $matchingClassPrograms[] = $classProgram;
                }
            }
        }

        if (empty($matchingClassPrograms)) {
            return new JsonResponse(['hydra:description' => 'No class programs found for the specified level and speciality or class.'], 400);
        }

//        $speciality = $this->specialityRepository->find($this->getIdFromApiResourceId($modelData['speciality'] ?? ''));
//        $level = $this->levelRepository->find($this->getIdFromApiResourceId($modelData['level'] ?? ''));
//        $class = $this->schoolClassRepository->find($this->getIdFromApiResourceId($modelData['class'] ?? ''));
//
//        if ($speciality && $level) {
//            $classPrograms = $this->classProgramRepo->findOneBy(['speciality' => $speciality, 'level' => $level]);
//        } else {
//            $classPrograms = $this->classProgramRepo->findOneBy(['class' => $class]);
//        }
//
//        if (empty($classPrograms)) {
//            return new JsonResponse(['hydra:description' => 'No class programs found for the given criteria.'], 400);
//        }

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $teachers = ['Cm', 'Td', 'Tp'];

        $data->setInstitution($this->getUser()->getInstitution());
        $data->setYear($this->getUser()->getCurrentYear());
        $data->setUser($this->getUser());

        $this->manager->persist($data);
        $this->manager->flush();

        foreach ($daysOfWeek as $dayOfWeek) {
            foreach ($teachers as $teacher) {
                foreach ($matchingClassPrograms as $classProgram) {
                    $dayMethod = "is{$dayOfWeek}{$teacher}";
                    // $startMethod = "get{$dayOfWeek}Start{$teacher}";
                    // $endMethod = "get{$dayOfWeek}End{$teacher}";

                    if ($classProgram->$dayMethod()) {
                        $timeTableModelDay = new TimeTableModelDay();
                        $timeTableModelDay->setModel($data);
                        $timeTableModelDay->setDay($dayOfWeek);

                        $timeTableModelDay->setInstitution($this->getUser()->getInstitution());
                        $timeTableModelDay->setYear($this->getUser()->getCurrentYear());
                        $timeTableModelDay->setUser($this->getUser());

                        $timeTableModelDay->setIsChecked(true);

                        $this->manager->persist($timeTableModelDay);
                        $this->manager->flush();

                        $timeTableModelDays["{$dayOfWeek}{$teacher}"] = $timeTableModelDay;
                    }
                }
            }
        }

        foreach ($matchingClassPrograms as $classProgram) {
            $teacherCourses = $classProgram->getId();
            $teacherCourseRegistrations = $this->teacherCourseRegistrationRepo->findAll();
            foreach ($teacherCourseRegistrations as $teacherCourseRegistration){
                $teacherCourseLink = $teacherCourseRegistration->getCourse();
                     if ($teacherCourseLink->getId() === $teacherCourses) {
                         foreach ($teachers as $teacher) {
                             foreach ($daysOfWeek as $dayOfWeek) {
                                 $dayMethod = "is{$dayOfWeek}{$teacher}";
                                 $startMethod = "get{$dayOfWeek}Start{$teacher}";
                                 $endMethod = "get{$dayOfWeek}End{$teacher}";

                                 if ($classProgram->$dayMethod()) {
                                     if ($teacherCourseRegistration->getTeacher() != null) {
                                         $startDate = $data->getStartDate();
                                         $endDate = $data->getEndDate();
                                         $currentDate = clone $startDate;

                                         while ($currentDate <= $endDate) {
                                             if ($currentDate->format('l') === $dayOfWeek) {
                                                 $timeTableModelDayCell = new TimeTableModelDayCell();
                                                 $timeTableModelDayCell->setStartAt($classProgram->$startMethod());
                                                 $timeTableModelDayCell->setEndAt($classProgram->$endMethod());
                                                 $timeTableModelDayCell->setTeacher($teacherCourseRegistration->getTeacher());
                                                 $timeTableModelDayCell->setCourse($classProgram);
                                                 $timeTableModelDayCell->setRoom($classProgram->getPrincipalRoom());
                                                 $timeTableModelDayCell->setModel($data);
                                                 $timeTableModelDayCell->setModelDay($this->timeTableModelDayRepo->findOneBy(['day' => $dayOfWeek]));

                                                 $timeTableModelDayCell->setInstitution($this->getUser()->getInstitution());
                                                 $timeTableModelDayCell->setYear($this->getUser()->getCurrentYear());
                                                 $timeTableModelDayCell->setUser($this->getUser());

                                                 $timeTableModelDayCell->setDate(clone $currentDate);

                                                 $this->manager->persist($timeTableModelDayCell);
                                             }
                                             $currentDate->modify('+1 day');
                                         }
                                     }
                                 }
                             }
                         }
                     } else {
                         // Skip to the next teacher registration
                         continue;
                     }

          }
        }

        $this->manager->flush();
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

}





























    








