# mitxela's monopoly

What you have found here is the source code to an absurdity.

It is difficult to justify exactly why I ended up creating this, but cast your mind back to 2012 and try to imagine that I was in a very difficult situation. To put it lightly, I was utterly despondent, the type of hopeless despair that we daren't translate into words. Again and again the rug was pulled out from under me, and everyone I thought might care turned away.

I was miserable. I gave up. I thought to Hell with it, I'm going to stop caring what people think, I'm going to do what _I_ want to do. And what I want to do... is create a browser-based online monopoly game.

![Monopoly screenshot](https://mitxela.com/img/uploads/Monopoly5.png)

Javascript, MySQL, PHP, the tiny .gif animations, the 3D board from first principles - I created it all. At the end of it, I don't think I felt any better, but now I was miserable and had also made a monopoly game.

The code was written for an environment running PHP 4 with magic quotes enabled (shudder). Over the years I added some shims to keep the software working but with the move to PHP8 there are too many backwards-incompatible changes and I think it's now time to pull the plug. In the decade that mitxela's monopoly was live, 1630 games were played (433 of which were long enough to overflow the game log) building a combined total of 25323 houses and 3593 hotels. Throughout all the games, the richest player ever was "kassie", amassing an astounding fortune of £39851. 

If for some reason you want to run this code, the repo includes a shim that emulates the older MySQL functions, so it should just about run under PHP7, if `display_errors` is off. Long polling is disabled by default, uncomment the block at line 883 of `main.php` to enable it.

As mentioned on [the project page at the time](https://mitxela.com/projects/monopoly), the code style is deliberately obtuse and borders on obfuscated. It was never supposed to be read by anyone else. This predates my adoption of version control, but based on last modified dates, the majority of the code was written through April and May of 2013.
