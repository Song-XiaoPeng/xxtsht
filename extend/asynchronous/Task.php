<?php
namespace asynchronous;

class Task{
    protected $stack;
    protected $routine;

    public function __construct(Generator $routine){
        $this->routine = $routine;
        $this->stack = new SplStack();
    }

    /**
     * [run 协程调度]
     * @return [type]         [description]
     */
    public function run(){
        $routine = &$this->routine;

        try {
            if(!$routine){
                return;
            }

            $value = $routine->current(); 

            //嵌套的协程
            if ($value instanceof Generator) {
                $this->stack->push($routine);
                $routine = $value;
                return;
            }

            //嵌套的协程返回
            if(!$routine->valid() && !$this->stack->isEmpty()) {
                $routine = $this->stack->pop();
            }

            $routine->next();

        } catch (Exception $e) {

            if ($this->stack->isEmpty()) {
                //throw the exception 
                return;
            }
        }
    }

    /**
     * [isFinished 判断该task是否完成]
     * @return boolean [description]
     */
    public function isFinished(){
        return $this->stack->isEmpty() && !$this->routine->valid();
    }

    public function getRoutine(){
        return $this->routine;
    }
}