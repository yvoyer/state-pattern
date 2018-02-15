<?php

namespace Star\Component\State\Builder;

use Star\Component\State\StateMachine;
use Star\Component\State\States\ArrayState;
use Star\Component\State\States\StringState;
use Star\Component\State\TransitionRegistry;
use Star\Component\State\Transitions\OneToOneTransition;

/**
 * Tool to build the StateMachine.
 */
final class StateBuilder
{
    /**
     * @var TransitionRegistry
     */
    private $registry;

    private function __construct()
    {
        $this->registry = new TransitionRegistry();
    }

    /**
     * @param string $name
     * @param string|string[] $from
     * @param string $to
     *
     * @return StateBuilder
     */
    public function allowTransition($name, $from, $to)
    {
        if (is_array($from)) {
            $state = new ArrayState(
                array_map(
                    function ($name) {
                        return new StringState($name);
                    },
                    $from
                )
            );
        } else {
            $state = new StringState($from);
        }

        $this->registry->addTransition(
            new OneToOneTransition($name, $state, new StringState($to))
        );

        return $this;
    }

    /**
     * @param string $attribute The attribute
     * @param string|string[] $states The list of states that this attribute applies to
     *
     * @return StateBuilder
     */
    public function addAttribute($attribute, $states)
    {
        $states = (array) $states;
        foreach ($states as $stateName) {
            $state = $this->registry->getState($stateName);
            $state->addAttribute($attribute);
        }

        return $this;
    }

    /**
     * @param string $currentState
     *
     * @return StateMachine
     */
    public function create($currentState)
    {
        return new StateMachine($currentState, $this->registry);
    }

    /**
     * @return StateBuilder
     */
    public static function build()
    {
        return new static();
    }
}
