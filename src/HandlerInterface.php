<?php

namespace iansltx\DialogflowBridge;

interface HandlerInterface
{
    public function __invoke(Question $question, Answer $answer) : Answer;
}
