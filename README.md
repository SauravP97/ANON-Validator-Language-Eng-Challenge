# AnonValidator

## Problem Statement :
ANON (Another Notorious Object Notation) is a madeup configuration format very similar to JSON. We need to build a validator that validates whether a given ANON String is syntactically correct or not.

## How to Test :
  1. Host this web application and open the **index.php** file to test the ANON Parser

## Directory Contents : 
  1. **parser** : Contains the Parser Business Logic
  2. **images** : Contains sample images
  3. **testcases** : Contains multiple test cases i.e. Sample ANON String files
  4. **index.php** : Frontend for the Parser Application  

## Approach :
I have built an ANON Parser that parses a given ANON String if it is syntactically correct. If the ANON String is invalid then it notifies you about the same. I have implemented a recursive approach to build the validator. It starts from the root and goes to the depth in case of multiple nested ANON Objects present. The parser converts the ANON String into a recursive tree and then validates each and every ANON Object present at its node. When the validation succeeds then it gives out the processed ANON Object as the response. If the parser fails at any point then it return the following error message **"Invalid ANON Object"**.

The process of conversion of a valid ANON string to the recursive tree looks like this :

![Parse Tree](/images/anon-parse-tree.png)

## Sample Outputs :

![Parse Sample 1](/images/parser-sample-1.png)
![Parse Sample 2](/images/parser-sample-2.png)
![Parse Sample 3](/images/parser-sample-3.png)

## Sample Video

![Parse Sample Video](/video/parser-sampl-gif.gif)
