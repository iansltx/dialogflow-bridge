<?php

namespace iansltx\ApiAiBridge;

interface HandlerInterface
{
    public function __invoke(Question $question, Answer $answer) : Answer;
}
