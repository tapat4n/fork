* `SIGHUP` - 
**This signal is sent at the end of the user terminal connection (normal or abnormal), usually at the end of the terminal control process,
Inform each job in the same session that they are no longer associated with the control terminal.**

* `SIGINT` -
**The interrupt signal is sent when the user types the intr character (usually ctrl-c).**

* `SIGQUIT` -
**Similar to SIGINT, but controlled by the quit character (usually Ctrl – /); When the process exits due to sigquit, it will generate a core file,
In this sense, it is similar to a program error signal.**

* `SIGKILL` -
**Used to end a program immediately. This signal cannot be blocked, processed or ignored. If the administrator finds that a process cannot be terminated, he can try to send this signal.**

* `SIGTERM` -
**Different from sigkill, it can be blocked and processed. It is usually used to ask the program to exit normally,
The shell command kill generates this signal by default. If the process cannot be terminated, we will try sigkill.**

* `SIGUSR1` -
**Leave it to the user**

* `SIGUSR2` -
**Leave it to the user**

* `SIGALRM` -
**The clock timing signal calculates the actual time or clock time. The alarm function uses the signal.**

* `SIGCHLD` -
**When the child process ends, the parent process receives this signal.**
