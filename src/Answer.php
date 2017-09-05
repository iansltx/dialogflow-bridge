<?php

namespace iansltx\ApiAiBridge;

class Answer implements \JsonSerializable
{
    protected $questionContextNames;

    protected $source;
    protected $outputContexts;

    protected $speech;
    protected $text;
    protected $data = [];

    public function __construct(array $questionContextNames = [], string $source = 'iansltx/api-ai-bridge')
    {
        $this->questionContextNames = $questionContextNames;
        $this->source = $source;
    }

    public function clone() : self
    {
        return clone $this;
    }

    public function dropAllQuestionContexts(bool $unsetLocallySet = false)
    {
        foreach ($this->questionContextNames as $name) {
            if (!isset($this->outputContexts[$name]) || $unsetLocallySet) {
                $this->outputContexts[$name] = ['name' => $name, 'lifespan' => 0, 'parameters' => (object) []];
            }
        }
        return $this;
    }

    public function setSpeech(string $speech) : self
    {
        $this->speech = $speech;
        return $this;
    }

    public function setText(string $text) : self
    {
        $this->text = $text;
        return $this;
    }

    public function setBoth(string $textAndSpeech) : self
    {
        $this->speech = $textAndSpeech;
        $this->text = $textAndSpeech;
        return $this;
    }

    public function getSpeech() : ?string
    {
        return $this->speech;
    }

    public function getText() : ?string
    {
        return $this->text;
    }

    public function getData() : array
    {
        return $this->data;
    }

    public function setData(array $data) : self
    {
        $this->data = $data;
        return $this;
    }

    public function setContext(string $name, array $data, int $lifespan) : self
    {
        $this->outputContexts[$name] = ['name' => $name, 'parameters' => $data ?: (object) [], 'lifespan' => $lifespan];
        return $this;
    }

    /** @inheritdoc */
    public function jsonSerialize()
    {
        // having problems with downstream clients not liking output for some reason? Try transliterating to ASCII
        // via either iconv or, if you're using a platform that doesn't have transliteration in iconv (e.g. Alpine
        // Linux), install php-intl and transliterate speech/text via \Transliterator::create('Latin-ASCII;').
        return [
            'speech' => $this->speech ?: '',
            'displayText' => $this->text ?: '',
            'data' => $this->data ?: (object) [],
            'contextOut' => array_values($this->outputContexts),
            'source' => $this->source
        ];
    }
}
