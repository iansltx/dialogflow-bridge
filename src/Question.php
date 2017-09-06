<?php

namespace iansltx\ApiAiBridge;

use Psr\Http\Message\ServerRequestInterface;

class Question
{
    const SKIP_NEVER = 0;
    const SKIP_IF_PARAM_EXISTS = 1;
    const SKIP_IF_CONTEXT_EXISTS = 2;
    const SKIP_IF_EITHER_EXISTS = self::SKIP_IF_PARAM_EXISTS | self::SKIP_IF_CONTEXT_EXISTS;

    protected $data;

    public static function fromRequest(ServerRequestInterface $request) : Question
    {
        return new self(json_decode($request->getBody()->getContents(), JSON_OBJECT_AS_ARRAY));
    }

    /**
     * @param array $inputData json_decode()d-as-array web hook request payload from api.ai
     */
    public function __construct(array $inputData)
    {
        $this->data = $inputData;
    }

    public function getParam(string $paramName, $default = null)
    {
        return isset($this->data['result']['parameters'][$paramName]) && $this->data['result']['parameters'][$paramName]
            ? $this->data['result']['parameters'][$paramName] : $default;
    }

    /**
     * Searches both direct params and contexts, direct params first, for
     * a value. Returns the first value found at that parameter index,
     * or $default if no parameter exists at that index for either direct
     * params or any context params.
     *
     * @param string $paramName
     * @param null $default
     * @return mixed
     */
    public function getAnyParam(string $paramName, $default = null)
    {
        if (isset($this->data['result']['parameters'][$paramName])) {
            return $this->data['result']['parameters'][$paramName];
        }

        foreach ($this->data['result']['contexts'] as $context) {
            if (isset($context['parameters'][$paramName]) && $context['parameters'][$paramName]) {
                return $context['parameters'][$paramName];
            }
        }

        return $default;
    }

    public function getContextParam(string $contextName, string $paramName, $default = null)
    {
        return isset($this->data['result']['contexts'][$contextName]['parameters'][$paramName]) &&
            $this->data['result']['contexts'][$contextName]['parameters'][$paramName] ?
            $this->data['result']['contexts'][$contextName]['parameters'][$paramName] : $default;
    }

    public function getContextData(string $contextName, $default = []) : array
    {
        return $this->data['result']['contexts'][$contextName]['parameters'] ?? $default;
    }

    public function hasContext(string $contextName) : bool
    {
        return isset($this->data['result']['contexts'][$contextName]);
    }

    /**
     * @param string $contextName
     * @return int how many requests the context is valid for; 1 means only this request,
     *   0 means the context does not exist
     */
    public function getContextLifespan(string $contextName) : int
    {
        return isset($this->data['result']['contexts'][$contextName]) ?
            $this->data['result']['contexts'][$contextName]['lifespan'] : 0;
    }

    public function getAction() : string
    {
        return $this->data['result']['action'];
    }

    public function isActionIncomplete() : bool
    {
        return $this->data['result']['actionIncomplete'];
    }

    public function getIntent() : string
    {
        return $this->data['result']['metadata']['intentName'];
    }

    public function getLanguageCode() : string
    {
        return $this->data['lang'];
    }

    public function getSessionId() : string
    {
        return $this->data['sessionId'];
    }

    public function getBaseAnswer() : Answer
    {
        return new Answer(array_column($this->data['result']['contexts'] ?? [], 'name'));
    }

    public function getOriginalText() : string
    {
        return $this->data['result']['resolvedQuery'] ?? '';
    }

    /**
     * @return array of original webhook request data, as it arrived (after decoding JSON as an array)
     */
    public function getRawRequestData() : array
    {
        return $this->data;
    }

    /**
     * Returns a new Question with the specified base parameter set to the requested value.
     * If the parameter already exists, $overwriteFlags determine behavior.
     *
     * @param string $paramName
     * @param $paramValue
     * @param int $skipOverwriteFlags one or more SKIP_* contsants. Default is to overwrite
     *   a value no matter what. Can also specify SKIP_IF_CONTEXT_EXISTS, SKIP_IF_PARAM_EXISTS,
     *   or an OR of both, aka SKIP_IF_EITHER_EXISTS.
     * @return static; if the object wasn't changed, the original instance will be returned
     */
    public function withParam(string $paramName, $paramValue, int $skipOverwriteFlags = self::SKIP_NEVER) : self
    {
        if (($skipOverwriteFlags & self::SKIP_IF_PARAM_EXISTS) &&
                isset($this->data['result']['parameters'][$paramName]) &&
                $this->data['result']['parameters'][$paramName]) {
            return $this;
        }

        if ($skipOverwriteFlags & self::SKIP_IF_CONTEXT_EXISTS) {
            foreach ($this->data['result']['contexts'] as $context) {
                if (isset($context['parameters'][$paramName]) && $context['parameters'][$paramName]) {
                    return $this;
                }
            }
        }

        $clone = clone $this;
        $clone->data['result']['parameters'][$paramName] = $paramValue;

        return $clone;
    }
}
