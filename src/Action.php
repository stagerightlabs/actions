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
     * The input keys used in the action that are not required.
     *
     * @return array
     */
    public function optional()
    {
        return [];
    }

    /**
     * The input keys used in the action that are not required.
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
        $self = new static;

        $missing = $self->missingInputKeys($input);

        if (! empty($missing)) {
            return $self
                ->fail("Missing expected keys: " . implode(', ', $missing));
        }

        $extraneous = $self->extraneousInputKeys($input);

        if (!empty($extraneous)) {
            return $self
                ->fail("Extraneous keys: " . implode(', ', $missing));
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
     * @return bool
     */
    public function completed()
    {
        return $this->hasCompleted == true;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return $this->hasCompleted == false;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Retrieve the payload.
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Retrieve an item from the payload.
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->payload)) {
            return;
        }

        return $this->payload[$key];
    }

    /**
     * Add an item to the payload.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->payload[$key] = $value;
    }

    /**
     * An alias for the 'set' method
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function save($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Does this action have a payload with contents?
     *
     * @return boolean
     */
    public function hasPayload()
    {
        return ! empty($this->payload);
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
     * Generate an array representation of this action.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'completed' => $this->hasCompleted,
            'name' => get_called_class(),
            'message' => $this->message,
            'payload' => $this->payload,
        ];
    }

    public function toString()
    {

    }
}
