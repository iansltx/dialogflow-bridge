<?php

namespace iansltx\ApiAiBridge;

class Answer implements \JsonSerializable
{
    protected $questionContextNames;

    protected $source;
    protected $outputContexts = [];

    protected $speech;
    protected $text;
    protected $data = [];

    public function __construct(array $questionContextNames = [], string $source = 'iansltx/api-ai-bridge')
    {
        $this->questionContextNames = $questionContextNames;
        $this->source = $source;
    }

    public function withoutQuestionContexts(bool $unsetLocallySet = false)
    {
        $clone = clone $this;
        foreach ($clone->questionContextNames as $name) {
            if (!isset($clone->outputContexts[$name]) || $unsetLocallySet) {
                $clone->outputContexts[$name] = ['name' => $name, 'lifespan' => 0, 'parameters' => (object) []];
            }
        }
        return $clone;
    }

    public function withSpeech(string $speech) : self
    {
        $clone = clone $this;
        $clone->speech = $speech;
        return $clone;
    }

    public function withText(string $text) : self
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }

    public function withSpeechAndText(string $textAndSpeech) : self
    {
        $clone = clone $this;
        $clone->speech = $textAndSpeech;
        $clone->text = $textAndSpeech;
        return $clone;
    }

    public function withData(array $data) : self
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    public function withContext(string $name, array $data, int $lifespan) : self
    {
        $clone = clone $this;
        $clone->outputContexts[$name] = ['name' => $name, 'parameters' => $data ?: (object)[], 'lifespan' => $lifespan];
        return $clone;
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
