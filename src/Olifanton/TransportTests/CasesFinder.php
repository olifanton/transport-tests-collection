<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use danog\ClassFinder\ClassFinder;

final class CasesFinder
{
    /**
     * @return array<string, class-string<TestCase>>
     * @throws \Throwable
     */
    public static function getCases(): array
    {
        $classes = ClassFinder::getClassesInNamespace(
            "Olifanton\\TransportTests\\Cases",
            ClassFinder::RECURSIVE_MODE,
        );
        $cases = [];

        foreach ($classes as $class) {
            if ($caseAttribs = (new \ReflectionClass($class))->getAttributes(AsCase::class)) {
                /** @var AsCase $caseConfig */
                $caseConfig = $caseAttribs[0]->newInstance();
                $cases[$caseConfig->alias] = $class;
            }
        }

        return $cases;
    }
}
