<?php

/** @test
    @score  0 */
function compiles() { 
    global $files;
    global $cflags;
    return compile("g++ $cflags -I. $files[0] -o prog"); 
}

/** @test
    @score 0
    @prereq compiles */
function january_december() {
    return run_and_check( "1\n1966\n", "1/1966 has 31 days.","30 days") 
       &&  run_and_check( "1\n1987\n", "1/1987 has 31 days.","30 days")
       &&  run_and_check( "1\n1994\n", "1/1994 has 31 days.","30 days")
       &&  run_and_check("12\n1983\n","12/1983 has 31 days.","30 days")
       &&  run_and_check("12\n1808\n","12/1808 has 31 days.","30 days")
       &&  run_and_check("12\n1745\n","12/1745 has 31 days.","30 days");
}

/** @test
    @score  0
    @prereq compiles */
function march_april() { 
    return run_and_check("3\n1957\n","3/1957 has 31 days.","30 days") 
       &&  run_and_check("3\n1962\n","3/1962 has 31 days.","30 days")
       &&  run_and_check("3\n1900\n","3/1900 has 31 days.","30 days")
       &&  run_and_check("4\n1952\n","4/1952 has 30 days.","31 days")
       &&  run_and_check("4\n1911\n","4/1911 has 30 days.","31 days")
       &&  run_and_check("4\n1244\n","4/1244 has 30 days.","31 days");
}

/** @test
    @score  0
    @prereq january_december march_april */
function format() { 
    return run("prog","3\n2012\n",$output1)
       &&  output_contains_lines($output1,<<<END
Month: <span class=input>3</span>
Year: <span class=input>2012</span>

3/2012 has 31 days.
END
) && run("prog","4\n2009\n",$output2)
       &&  output_contains_lines($output2,<<<END
Month: <span class=input>4</span>
Year: <span class=input>2009</span>

4/2009 has 30 days.
END
);
} 

/** @test
    @score  0
    @prereq format */
function may_june() { 
    return run_and_check("5\n1931\n","5/1931 has 31 days.","30 days") 
       &&  run_and_check("5\n1913\n","5/1913 has 31 days.","30 days")
       &&  run_and_check("5\n1942\n","5/1942 has 31 days.","30 days")
       &&  run_and_check("6\n1924\n","6/1924 has 30 days.","31 days")
       &&  run_and_check("6\n1976\n","6/1976 has 30 days.","31 days")
       &&  run_and_check("6\n2003\n","6/2003 has 30 days.","31 days");
}

/** @test
    @score  0
    @prereq format */
function july_august_september() { 
    return run_and_check("7\n1922\n","7/1922 has 31 days.","30 days") 
       &&  run_and_check("7\n1898\n","7/1898 has 31 days.","30 days")
       &&  run_and_check("7\n1947\n","7/1947 has 31 days.","30 days")
       &&  run_and_check("8\n1956\n","8/1956 has 31 days.","30 days")
       &&  run_and_check("8\n1977\n","8/1977 has 31 days.","30 days")
       &&  run_and_check("8\n1903\n","8/1903 has 31 days.","30 days")
       &&  run_and_check("9\n1999\n","9/1999 has 30 days.","31 days")
       &&  run_and_check("9\n1961\n","9/1961 has 30 days.","31 days")
       &&  run_and_check("9\n2008\n","9/2008 has 30 days.","31 days");}

/** @test
    @score  0
    @prereq may_june july_august_september */
function october_november() { 
    return run_and_check("10\n1924\n","10/1924 has 31 days.","30 days") 
       &&  run_and_check("10\n2003\n","10/2003 has 31 days.","30 days")
       &&  run_and_check("10\n1942\n","10/1942 has 31 days.","30 days")
       &&  run_and_check("11\n1924\n","11/1924 has 30 days.","31 days")
       &&  run_and_check("11\n1976\n","11/1976 has 30 days.","31 days")
       &&  run_and_check("11\n2003\n","11/2003 has 30 days.","31 days");
}

/** @test
    @score  0
    @prereq october_november */
function february() { 
    return run_and_check("2\n1992\n","2/1992 has 29 days.","28 days")
       &&  run_and_check("2\n1980\n","2/1980 has 29 days.","28 days")
       &&  run_and_check("2\n2012\n","2/2012 has 29 days.","28 days")
       &&  run_and_check("2\n1896\n","2/1896 has 29 days.","28 days")
       &&  run_and_check("2\n1904\n","2/1904 has 29 days.","28 days")
       &&  run_and_check("2\n1600\n","2/1600 has 29 days.","28 days")
       &&  run_and_check("2\n2000\n","2/2000 has 29 days.","28 days")
       &&  run_and_check("2\n1993\n","2/1993 has 28 days.","29 days")
       &&  run_and_check("2\n1900\n","2/1900 has 28 days.","29 days")
       &&  run_and_check("2\n1902\n","2/1902 has 28 days.","29 days")
       &&  run_and_check("2\n1700\n","2/1700 has 28 days.","29 days")
       &&  run_and_check("2\n2011\n","2/2011 has 28 days.","29 days")
       &&  run_and_check("2\n2010\n","2/2010 has 28 days.","29 days")
       &&  run_and_check("2\n1994\n","2/1994 has 28 days.","29 days");

}

/** @test
    @prereq february
    @score  1.0 */
function points()
{
    return _count_case(true);
}


include 'scoring_functions.php';
include 'auto_score.php';


