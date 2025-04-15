<?php
include "common.php";
essay_begin($parts_of_a_pl_implementation_page);
?>

<p>I've tried a number of times to implement a programming language. None of
these attempts have succeeded, not because I got stuck, but because of other
reasons. Sometimes I realize the idea behind the language just won't work.
Other times I get bored of the project because it doesn't feel novel
enough. Still other times I give up because it feels like it will take a
monumental amount of work before it becomes at all useful. This page contains
the checklist of things to figure out before starting the next project so that
it is more likely to succeed.</p>

<p>Each of these items overlaps and interacts with the other ones. In other
words, this page doesn't describe a step-by-step procedure to designing a
language. Rather, it's just a compilation of a bunch of different points that I
find myself revisiting every time I contemplate starting a new language. I
think you have to come up with an answer for each item and then check to make
sure they are all mutually compatible.</p>

<h2>Have a compelling use-case</h2>

<p>Without something that a language will be used for, it's hard to be
motivated to complete it, and it is also hard to keep the scope of the project
targetted enough that it <em>can</em> be completed. Note that it doesn't have
to be that case that the only or even easiest way to solve the use-case is by
making a language. What's important is that if the language exists, it would be
useful.</p>

<h2>Be novel in some respect</h2>

<p>If it is just another run-of-the-mill general-purpose programming language,
it will be very difficult to make something preferable to the many other
languages in existence. It must have some feature that does not exist in other
languages, or some combination of features that usually aren't available at the
same time.</p>

<h2>Choose a syntax</h2>

<p>This is a basic question that at this point I am a bit bored by. It would be
nice if the syntax could just be lisp-like and automatically derived from the
constructs of the language, but sometimes part of the benefit of the language
is brevity, in which case syntax could matter more. There's also more
outlandish possibilities such as choosing a non-textual way of constructing
programs; for example, block-based and node-wire diagrams are two other forms
of syntax that some languages have used.</p>

<h2>Choose a typing discipline</h2>

<p>Some languages dynamic and have no type system at all. Others have very
expressive static type systems. I find myself drawn to dependent types, but
there also seem to be a lot of research questions around them, so it's not a
clear win. I've also been drawn to the idea of having a base dynamic language
with a typed subset on top, mainly as a way of keeping the language modular.</p>

<h2>Choose a runtime model</h2>

<p>Decide on whether to implement a compiler or an interpreter. Also, decide if
it is worth the complexity to do something like monomorphization, or if it is
okay to pay the efficiency cost of having a uniform value representation.</p>

<h2>Choose a performance threshold</h2>

<p>Some use-cases require blazing-fast performance; others don't. It's
important to decide how much performance matters because if it matters very
little, certain shortcuts may be acceptable, which keeps the project scope from
getting out of control. It might even be acceptable to implement an interpreter
rather than a compiler.</p>

<h2>Choose a set of target platforms and an interaction mechanism</h2>

<p>Ultimately, programs aren't useful unless they can be run in some way; and
to make a program runnable, it must be determined how the program can produce
output and consume input. Some config languages merely expand to some normal
form; they are limited in what they can do, which is both a feature (sandboxing
is nice) and a bug (not as much can be accomplished). If the language is
supposed to be able to do IO, it's worth thinking about an FFI story; or
perhaps the language should not allow any FFI and just provide a sufficient
standard set of operations sufficient for the intended use-case.</p>

<?php essay_end() ?>
