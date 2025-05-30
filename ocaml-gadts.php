<?php
include "common.php";
essay_begin($ocaml_gadts_page);
?>

<p>OCaml has this weird feature of its type system called "Generalized
Algebraic Data Types", or GADTs, for short. Experts typically use this
feature to haze newcomers to the language (unintentionally, of course).
This page aims to fix this problem by enabling the reader to be the expert
inflicting the suffering, rather than the sufferer.</p>

<p>The first step I want to take is to re-frame your understanding of "normal"
variant constructors: constructors are just plain old functions. In OCaml,
this is a bit of a lie, but it's not <em>too</em> far off; we can't pass
constructors around as first-class functions, but building something with
them looks pretty similar to calling a function.</p>

<p>Here's the <code>option</code> type in OCaml.</p>

<pre>
<kw>type</kw> 'a <type>option</type> =
  | None
  | Some <kw>of</kw> 'a</pre>

<p>This is the syntax we would usually use, but there is an alternate syntax that
lets you write the constructor as if it were a function.</p>

<pre>
<kw>type</kw> 'a <type>option</type> =
  | None : 'a <type>option</type>
  | Some : 'a -> 'a <type>option</type></pre>

<p>That's really not too far from what you often see in OCaml module
signatures.</p>

<pre>
<kw>type</kw> 'a <type>option</type>

<kw>val</kw> none : 'a <type>option</type>
<kw>val</kw> some : 'a -> 'a <type>option</type></pre>

<p>What I just showed you was a translation from presenting
<code>option</code> as a list of constructors to presenting it as a list of
functions. You can go the other way too! Consider this subset of the
<code>List</code> module.</p>

<pre>
<kw>type</kw> 'a <type>list</type>

<kw>val</kw> empty : 'a <type>list</type>
<kw>val</kw> cons : 'a -> 'a <type>list</type> -> 'a <type>list</type>
<kw>val</kw> map : 'a <type>list</type> -> f:('a -> 'b) -> 'b <type>list</type></pre>

<p>Translating this back to constructors is not immediately straightforward
because constructors are only allowed to take a single parameter, so let's
start with the intermediate step of translating these curried functions
into functions that take a tuple of arguments.</p>

<pre>
type 'a list

<kw>val</kw> empty : 'a <type>list</type>
<kw>val</kw> cons : 'a * 'a <type>list</type> -> 'a <type>list</type>
<kw>val</kw> map : 'a <type>list</type> * ('a -> 'b) -> 'b <type>list</type></pre>

<p>Now it should be easy to translate these functions to constructors.</p>

<pre>
<kw>type</kw> 'a <type>list</type> =
  | Empty : 'a <type>list</type>
  | Cons : 'a * 'a <type>list</type> -> 'a <type>list</type>
  | Map : 'a <type>list</type> * ('a -> 'b) -> 'b <type>list</type></pre>

<p>It's perfectly understandable if the <code>Map</code> constructor is a
bit baffling. We're used to <code>List.map</code> being a function that
<em>does</em> something, but here we've just packaged it up as some data.
On the other hand, if you're comfortable with functions being first-class
values, then this shouldn't bother you at all; <code>Map</code> is just an
ordinary constructor that contains two pieces of sub-data.</p>

<p>One way to look at constructors is that they are functions that don't do
anything in the moment, but then later you can look at them to see what
parameters they were called with. We can pattern match on the fancy list
type and eliminate the <code>Map</code> constructor by actually doing the
mapping.</p>

<pre>
<kw>let</kw> <kw>rec</kw> do_maps : <kw>type</kw> a. (a -> b) -> a <type>list</type> -> b <type>list</type> =
  <kw>fun</kw> f xs ->
    <kw>match</kw> xs <kw>with</kw>
    | Empty -> Empty
    | Cons (x, xs) -> Cons (f x, do_maps f xs)
    | Map (xs, g) -> do_maps (<kw>fun</kw> x -> f (g x)) xs</pre>

<p>Don't worry if this looks foreign; it's a really weird function. In
fact, it's even weirder than you probably realize; the type inference
algorithm is hiding some of the nuance of this code. The key point of
intrigue is in what type the <code>xs</code> variable has in the pattern
<code>Map (xs, g)</code>; it's worth pondering for a minute or two before
reading on.</p>

<p>The answer is that the compiler has to make up a temporary abstract type
for the items of the list, so <code>xs</code> has a type like <code>$1 <type>list</type></code>.
The <code>$1</code> is the type of the list items when
<code>Map</code> was originally called, but the compiler doesn't remember
what the type is, so the body of that match case has to treat it as an
abstract type. In other words, we know there <em>exists</em> a type for the
items of that list, but we don't know what it is. Weird, huh?</p>

<h2>Normal variants are more limited than GADTs.</h2>

<p>This is the section of the post where we start talking about GADTs. Just
kidding! We've been talking about GADTs this whole time; did you
notice? GADTs are not a new kind of thing, but rather an extension of
the abilities of an existing thing, namely variants.</p>

<p>The view that constructors are like functions is helpful for seeing the
extra power that GADTs provide. The ordinary <code><kw>of</kw></code>-syntax for
variants requires any type variables in the constructor to be matched by
type variables in the type itself. For example, here is some invalid code
in which we try to define the fancy list type with <code><kw>of</kw></code>-syntax.</p>

<pre>
<kw>type</kw> 'a <type>list</type> =
  | Empty
  | Cons <kw>of</kw> 'a * 'a <type>list</type>
  | Map <kw>of</kw> 'x <type>list</type> * ('x -> 'a)</pre>

<p>The <code>Map</code> constructor here is illegal because it mentions a
<code>'x</code> variable that wasn't mentioned in <code><type>list</type></code>'s type
parameters.</p>

<p>Another limitation on constructor "functions" enforced by
<code><kw>of</kw></code>-syntax is that the result of the function is always the
type itself, with its unadorned type variables. To illustrate this, let us
try to translate the <code>zip</code> function to a constructor.</p>

<pre>
<kw>val</kw> zip : 'a <type>list</type> -> 'b <type>list</type> -> ('a * 'b) <type>list</type></pre>

<p>Doing it with the new syntax we learned is easy.</p>

<pre>
  | Zip : 'a <type>list</type> * 'b <type>list</type> -> ('a * 'b) <type>list</type></pre>

<p>It should be similarly easy to do this with <code><kw>of</kw></code>-syntax, right?
After all, all the type variables in the parameters <em>do</em> show up in
the result.</p>

<pre>
  | Zip <kw>of</kw> 'a <type>list</type> * 'b <type>list</type></pre>

<p>Wait, but how do we say that the result is <code>('a * 'b) list</code>?
That's exactly the problem. We might try to add it in the type declaration.</p>

<pre>
<kw>type</kw> ('a * 'b) <type>list</type> =
  | Zip <kw>of</kw> 'a <type>list</type> * 'b <type>list</type></pre>

<p>But this is not allowed.</p>

<h2>Why are GADTs scary?</h2>

<p>I think the idea that constructors are like functions is pretty
approachable. So why do people find GADTs so scary? One reason is that
people often given scary explanations of GADTS, sadly. However, another
reason is that they are legitimately more complicated to use. It turns out
that the limitations afforded by the <code><kw>of</kw></code>-syntax of normal
variants prevents weird type situations (like with <code>do_maps</code>)
from arising.</p>

<pre>
<kw>let</kw> <kw>rec</kw> do_maps : <kw>type</kw> a. (a -> b) -> a <type>list</type> -> b <type>list</type> =
  <kw>fun</kw> f xs ->
    <kw>match</kw> xs <kw>with</kw>
    | Empty -> Empty
    | Cons (x, xs) -> Cons (f x, do_maps f xs)
    | Map (xs, g) -> do_maps (<kw>fun</kw> x -> f (g x)) xs</pre>

<p>We already mentioned that the compiler has to make up some types in the
middle of this function. However, we didn't address another crucial piece
of this code.</p>

<pre>
<kw>let</kw> <kw>rec</kw> do_maps : <em><kw>type</kw> a. (a -> b) -> a <type>list</type> -> b <type>list</type></em> =
  ...</pre>

<p>While this may look like any other type annotation, it's actually a special
kind that allows this function to do "polymorphic recursion". The reason is
that the recursive calls in <code>do_maps</code> may have to run on lists
of many different types, due to the flexibility added by the
<code>Map</code> constructor.</p>

<p>Understanding every part of this example is unnecessary to grasp the point
that GADTs do make things more complicated.</p>

<h2>When should I use GADTs?</h2>

<p>This is the wrong question. You shouldn't be choosing between normal
variants and GADTs. Instead, think of constructors as functions whose calls
you want to inspect later. You'll know you need a GADT if the type
signatures of those functions violate the rules of OCaml's
<code><kw>of</kw></code>-syntax. (the compiler will tell you when that happens)</p>

<p>Fortunately, most variants don't need the extra power. It would be sad
if they did, since several things get worse with GADTs. For instance, you
need more type annotations to make the compiler happy. Related to this,
OCaml's <code>ppx_deriving</code> extensions don't work because they don't
have access to type information, so they can't generate the proper type
information required by generated code.</p>

<p>To me, the bottom line is that you should use them if they are useful, with
a slight bias toward avoiding them, since they tend to increase code noise.</p>

<?php essay_end() ?>
