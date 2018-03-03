<?php
/**
 * This file is part of the php-state project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\State;

use PHPUnit\Framework\TestCase;
use Star\Component\State\Transitions\ManyToOneTransition;
use Star\Component\State\Transitions\OneToOneTransition;

final class TransitionRegistryTest extends TestCase
{
    /**
     * @var TransitionRegistry
     */
    private $registry;

    public function setUp()
    {
        $this->registry = new TransitionRegistry();
    }

    /**
     * @expectedException        \Star\Component\State\NotFoundException
     * @expectedExceptionMessage The transition 'not-found' could not be found.
     */
    public function test_it_should_throw_exception_when_transition_is_not_registered()
    {
        $this->registry->getTransition('not-found');
    }

    public function test_it_should_throw_exception_when_state_is_not_registered()
    {
        $this->assertFalse($this->registry->hasState('not-found'));
    }

    public function test_it_should_add_transition()
    {
        $this->registry->addTransition(
            new OneToOneTransition('name', 'from', 'to')
        );
        $transition = $this->registry->getTransition('name');
        $this->assertInstanceOf(StateTransition::class, $transition);
    }

    public function test_it_should_contain_the_states()
    {
        $this->assertFalse($this->registry->hasState('from'));
        $this->registry->registerStartingState('t', 'from');
        $this->assertTrue($this->registry->hasState('from'));
    }

    public function test_it_should_merge_attributes_when_duplicate_state_is_registered()
    {
        $this->registry->registerStartingState('t1', 'from');
        $this->assertFalse($this->registry->hasAttribute('from', 'attr'));
        $this->assertFalse($this->registry->hasAttribute('from', 'other'));

        $this->registry->registerStartingState('t2', 'from', ['attr']);

        $this->assertTrue($this->registry->hasAttribute('from', 'attr'));
        $this->assertFalse($this->registry->hasAttribute('from', 'other'));

        $this->registry->registerStartingState('t3', 'from', ['other']);

        $this->assertTrue($this->registry->hasAttribute('from', 'attr'));
        $this->assertTrue($this->registry->hasAttribute('from', 'other'));
    }

    /**
     * @expectedException        \Star\Component\State\DuplicateEntryException
     * @expectedExceptionMessage The transition 'duplicate' is already registered.
     */
    public function test_it_should_throw_exception_when_duplicate_transition_is_registered()
    {
        $this->registry->addTransition(
            new OneToOneTransition('duplicate', 'from', 'to')
        );
        $this->registry->addTransition(
            new OneToOneTransition('duplicate', 'from', 'to')
        );
    }

    public function test_it_should_register_multiple_state_when_transition_has_multiple_source_state()
    {
        $this->registry->addTransition(
            new ManyToOneTransition('name', ['from1', 'from2'], 'to')
        );
        $this->assertTrue($this->registry->hasState('from1'));
        $this->assertTrue($this->registry->hasState('from2'));
        $this->assertTrue($this->registry->hasState('to'));
    }

    public function test_it_should_return_if_the_state_exists()
    {
        $this->registry->registerStartingState('t', 'exists');
        $this->assertTrue($this->registry->hasState('exists'));
        $this->assertFalse($this->registry->hasState('not-exists'));
    }

    public function test_it_should_return_whether_the_transition_starts_from_a_state()
    {
        $this->registry->addTransition(new OneToOneTransition('t', 'from', 'to'));

        $this->assertTrue($this->registry->transitionStartsFrom('t', 'from'));
        $this->assertFalse($this->registry->transitionStartsFrom('t', 'to'));
    }

    public function test_it_should_visit_the_transitions()
    {
        $visitor = $this->getMockBuilder(TransitionVisitor::class)->getMock();
        $visitor
            ->expects($this->once())
            ->method('visitTransition')
            ->with('t');
        $visitor
            ->expects($this->once())
            ->method('visitFromState')
            ->with('from', ['attr-1']);
        $visitor
            ->expects($this->once())
            ->method('visitToState')
            ->with('to', ['attr-2']);

        $this->registry->registerStartingState('t', 'from', ['attr-1']);
        $this->registry->registerDestinationState('t', 'to', ['attr-2']);
        $this->registry->acceptTransitionVisitor($visitor);
    }

    public function test_it_should_visit_the_states()
    {
        $visitor = $this->getMockBuilder(StateVisitor::class)->getMock();
        $visitor
            ->expects($this->at(0))
            ->method('visitState')
            ->with('from', ['attr']);
        $visitor
            ->expects($this->at(1))
            ->method('visitState')
            ->with('to', []);
        $this->registry->addTransition(new OneToOneTransition('t', 'from', 'to'));
        $this->registry->addAttribute('from', 'attr');

        $this->registry->acceptStateVisitor($visitor);
    }
}
