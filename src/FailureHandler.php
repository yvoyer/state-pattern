<?php
/**
 * This file is part of the php-state project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\State;

/**
 * Customize how the system will handle the failure points.
 */
interface FailureHandler
{
    /**
     * Launched when a not allowed transition is detected.
     *
     * @param StateContext $context
     * @param State $from
     * @param State $to
     */
    public function handleNotAllowedTransition(StateContext $context, State $from, State $to);
}