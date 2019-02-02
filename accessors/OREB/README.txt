
Q) What is this?
A) An accessor extension to pull data directly from the OREB RETS server.

Q) Where does it go?
A) In the Retriever's "accessors" folder.

Q) Anything else to know?
A) The phrets lib is very old and it leaks like a damn civ. You may get 500 errors, so just keep kicking it until it goes through (yikes).

Q) That sounds sketchy, any known fixes?
A) There is a newer version of phrets, but the developers insist on using Composer versions of PHP not officially supported by CentOS. So not really.