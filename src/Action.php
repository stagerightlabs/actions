<?php

namespace StageRightLabs\Actions;

abstract class Action
{
    /**
     * An array of data we are sending back to the requesting controller.
     *
     * @var array
     */
    protected $payload;

    /**
     * A string representing a message that we might want to convey to the user.
     *
     * @var string
     */
    protected $message;

    /**
     * A boolean representing an outcome status.
     *
     * @var bool
     */
    protected $hasCompleted = false;

    /**
     * The input keys used in this action that are not required.
     *
     * @return array
     */
    public function optional()
    {
        return [];
    }

    /**
     * The input keys required by this action.
     *
     * @return array
     */
    public function required()
    {
        return [];
    }

    /**
     * Trigger the execution of this action.
     *
     * @param array $input
     * @return self
     */
    public static function execute($input = [])
    {
        $isStatic = !(isset($this) && $this instanceof self);
        $self = $isStatic ? new static : $this;

        $missing = $self->missingInputKeys($input);

        if (! empty($missing)) {
            return $self
                ->fail("Missing expected keys: " . implode(', ', $missing));
        }

        $extraneous = $self->extraneousInputKeys($input);

        if (!empty($extraneous)) {
            return $self
                ->fail("Extraneous keys: " . implode(', ', $extraneous));
        }

        return $self->handle($input);
    }

    /**
     * Handle the action.
     *
     * @param Action|array $input
     * @return self
     */
    abstract public function handle($input = []);

    /**
     * Flag this action as having completed.
     *
     * @param string $message
     * @return self
     */
    protected function complete($message = '')
    {
        $this->hasCompleted = true;
        $this->message = $message;

        return $this;
    }

    /**
     * Flag this action as having failed.
     *
     * @param string $message
     * @return self
     */
    protected function fail($message = '')
    {
        $this->hasCompleted = false;
        $this->message = $message;

        return $this;
    }

    /**
     * Has this action completed?
     *
     * @return bool
     */
    public function completed()
    {
        return $this->hasCompleted == true;
    }

    /**
     * Has this action failed?
     *
     * @return bool
     */
    public function failed()
    {
        return $this->hasCompleted == false;
    }

    /**
     * Retrieve the status message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Return an array of expected keys that are missing from the input array.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    protected function missingInputKeys($array)
    {
        $missing = [];

        foreach ($this->required() as $expected) {
            if (!array_key_exists($expected, $array)) {
                array_push($missing, $expected);
            }
        }

        return $missing;
    }

    /**
     * Return an array of keys that were provided but not expected.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    protected function extraneousInputKeys($array)
    {
        $extraneous = [];
        $expected = array_merge($this->required(), $this->optional());

        foreach ($array as $key => $_value) {
            if (! in_array($key, $expected)) {
                array_push($extraneous, $key);
            }
        }

        return $extraneous;
    }

    /**
     * Generate a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        if (!empty($this->message)) {
            return $this->message;
        }

        if ($this->hasCompleted) {
            return 'Action completed.';
        }

        return 'Action failed.';
    }
}
