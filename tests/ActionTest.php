<?php

namespace StageRightLabs\Actions\Tests;

use PHPUnit\Framework\TestCase;
use StageRightLabs\Actions\Action;

class ActionTest extends TestCase
{
    /** @test */
    public function actions_can_execute_and_complete()
    {
        $action = CompletingExampleAction::execute();

        $this->assertInstanceOf(Action::class, $action);
        $this->assertInstanceOf(CompletingExampleAction::class, $action);
        $this->assertTrue($action->completed());
        $this->assertFalse($action->failed());
        $this->assertEquals(CompletingExampleAction::SUCCESS, $action->getMessage());
    }

    /** @test */
    public function actions_can_execute_and_fail()
    {
        $action = FailingExampleAction::execute();

        $this->assertInstanceOf(Action::class, $action);
        $this->assertInstanceOf(FailingExampleAction::class, $action);
        $this->assertFalse($action->completed());
        $this->assertTrue($action->failed());
        $this->assertEquals(FailingExampleAction::FAILURE, $action->getMessage());
    }

    /** @test */
    public function actions_can_accept_optional_input()
    {
        $action = OptionalInputExampleAction::execute([
            'foo' => 'bar'
        ]);

        $this->assertTrue($action->completed());
    }

    /** @test */
    public function actions_will_not_fail_when_optional_input_keys_are_missing()
    {
        $action = OptionalInputExampleAction::execute();

        $this->assertTrue($action->completed());
    }

    /** @test */
    public function actions_can_accept_required_input()
    {
        $action = RequiredInputExampleAction::execute([
            'baz' => 'bat'
        ]);

        $this->assertTrue($action->completed());
    }

    /** @test */
    public function actions_will_fail_when_required_input_keys_are_missing()
    {
        $action = RequiredInputExampleAction::execute();

        $this->assertFalse($action->completed());
    }

    /** @test */
    public function actions_will_fail_when_unknown_input_keys_are_provided()
    {
        $action = OptionalInputExampleAction::execute([
            'biff' => 'splat'
        ]);

        $this->assertFalse($action->completed());
    }

    /** @test */
    public function actions_can_return_a_payload()
    {
        $action = PayloadExampleAction::execute();

        $this->assertEquals('bar', $action->foo);
    }
}

class CompletingExampleAction extends Action
{
    const SUCCESS = 'the action has completed.';

    public function handle($input = [])
    {
        return $this->complete(self::SUCCESS);
    }
}

class FailingExampleAction extends Action
{
    const FAILURE = 'the action failed';

    public function handle($input = [])
    {
        return $this->fail(self::FAILURE);
    }
}

class OptionalInputExampleAction extends Action
{
    public function handle($input = [])
    {
        $message = array_key_exists('foo', $input)
            ? 'Foo input provided'
            : 'Foo input not provided';

        return $this->complete($message);
    }

    public function optional()
    {
        return [
            'foo'
        ];
    }
}

class RequiredInputExampleAction extends Action
{
    public function handle($input = [])
    {
        return $this->complete($input['baz']);
    }

    public function required()
    {
        return [
            'baz'
        ];
    }
}

class PayloadExampleAction extends Action
{
    public $foo;

    public function handle($input = [])
    {
        $this->foo = 'bar';
        return $this->complete();
    }
}
