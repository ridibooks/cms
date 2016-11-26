/**
 * Created by ridinfra on 2015-09-23.
 */
define([], function() {
  "use strict";
  var module = {};

  function Task(handler, params) {
    this.handler = handler;
    this.params = params;
  }

  function WorkerOption(options) {
    this.request_count = options.request_count || 1;
    this.beforeWork = options.beforeWork;
    this.pauseWork = options.pauseWork;
    this.resumeWork = options.resumeWork;
    this.afterWork = options.afterWork;
  }

  /**
   * @param {WorkerOption} worker_option
   * @constructor
   */
  function Worker(worker_option) {
    var task_queue = [];
    var pause_queue = [];
    /**
     * 동작중일경우와 task_queue가 빈상태인 경우와는 차이가 있음
     * 동작중: work가 시작되고서부터 마지막 task가 종료되었을때까지의 상태
     * task_queue가 빈상태: 마지막 task가 시작된 이후
     * @type {Array}
     */
    var is_working = [];

    function isWorking() {
      for (var i = 0; i < worker_option.request_count; i++) {
        if (is_working[i]) {
          return true;
        }
      }

      return false;
    }

    function isPaused() {
      return pause_queue.length !== 0;
    }

    function startTasks() {
      for (var i = 0; i < worker_option.request_count; i++) {
        if (is_working[i]) {
          continue;
        }

        is_working[i] = true;
        startTask(i);
      }
    }

    function startTask(request_id) {
      var callback = function() {
        if (0 < task_queue.length) {
          startTask(request_id);
        } else {
          is_working[request_id] = false;

          if (!isWorking()) {
            if (isPaused()) {
              if (worker_option.pauseWork) {
                worker_option.pauseWork();
              }
            } else {
              if (worker_option.afterWork) {
                worker_option.afterWork();
              }
            }
          }
        }
      };

      /* @type {Task} */
      var task = task_queue.shift();
      if (task !== undefined) {
        task.handler(task.params, callback);
      } else {
        // task가 존재하지 않을경우에는 해당 request를 종료하기 위해 callback을 호줄해준다.
        callback();
      }
    }

    function moveTaskQueueToPauseQueue() {
      while (task_queue.length) {
        pause_queue.push(task_queue.shift());
      }
    }

    /**
     * @param {Task} task
     */
    this.appendWork = function(task) {
      task_queue.push(task);
    };

    this.startWork = function() {
      if (worker_option.beforeWork) {
        worker_option.beforeWork();
      }

      startTasks();
    };

    this.resumeWork = function() {
      moveTaskQueueToPauseQueue();
      task_queue = pause_queue;
      pause_queue = [];

      startTasks();

      if (worker_option.resumeWork) {
        worker_option.resumeWork();
      }
    };

    this.pauseWork = function() {
      if (!isWorking()) {
        if (worker_option.pauseWork) {
          worker_option.pauseWork();
        }
      }

      moveTaskQueueToPauseQueue();
    };

    this.stopWork = function() {
      pause_queue = [];
      task_queue = [];

      if (!isWorking()) {
        if (worker_option.afterWork) {
          worker_option.afterWork();
        }
      }
    };

    /**
     * @returns {boolean}
     */
    this.isWorking = function() {
      return isWorking();
    };

    /**
     * @returns {boolean}
     */
    this.isPaused = function() {
      return isPaused();
    };

    /**
     * @returns {WorkerOption}
     */
    this.getWorkerOption = function() {
      return worker_option;
    };

    /**
     * @param {WorkerOption} new_worker_option
     */
    this.setWorkerOption = function(new_worker_option) {
      worker_option = new_worker_option;
    };
  }

  /**
   * @param options
   * @returns {Worker}
   */
  module.createWorker = function(options) {
    return new Worker(new WorkerOption(options || {}));
  };

  /**
   * @param handler
   * @param params
   * @returns {Task}
   */
  module.createTask = function(handler, params) {
    return new Task(handler, params);
  };

  return module;
});
