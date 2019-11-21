<?php
include 'scoring_functions.php';
include 'auto_score.php';

/** @test
    @score  0 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
       &&  check_blacklist($source);
}

//------------------------------------------------------------------------------

/** @test
    @score  0
    @prereq compiles */
function t15() {
	return run_and_check("15\n","Parking cost = $0","Parking cost = $2");
}

/** @test
    @score  0
    @prereq compiles */
function t30() {
	return run_and_check("30\n","Parking cost = $0","Parking cost = $2");
}

/** @test
    @score  0
    @prereq t15 t30 */
function t31() {
	return run_and_check("31\n","Parking cost = $2","Parking cost = $0");
}

/** @test
    @score  0
    @prereq t15 t30 */
function t45() {
	return run_and_check("45\n","Parking cost = $2","Parking cost = $0");
}

/** @test
    @score  0
    @prereq t15 t30 */
function t60() {
	return run_and_check("60\n","Parking cost = $2","Parking cost = $0");
}

/** @test
    @score  0
    @prereq t31 t45 t60 */
function t75() {
	return run_and_check("75\n","Parking cost = $3","Parking cost = $2");
}

/** @test
    @score  0
    @prereq t31 t45 t60 */
function t90() {
	return run_and_check("90\n","Parking cost = $3","Parking cost = $2");
}

/** @test
    @score  0
    @prereq t75 t90 */
function t135() {
	return run_and_check("135\n","Parking cost = $5","Parking cost = $2");
}

/** @test
    @score  0
    @prereq t75 t90 */
function t150() {
	return run_and_check("150\n","Parking cost = $5","Parking cost = $2");
}

/** @test
    @score  0
    @prereq t135 t150 */
function t158() {
	return run_and_check("158\n","Parking cost = $6","Parking cost = $1");
}

// ---------- 50 points ---------

/** @test
    @score  0
    @prereq t135 t150 */
function t194() {
	return run_and_check("194\n","Parking cost = $7","Parking cost = $1");
}

/** @test
    @score  0
    @prereq t135 t150 */
function t380() {
	return run_and_check("380\n","Parking cost = $13","Parking cost = $0");
}

/** @test
    @score  0
    @prereq t158 t194 t380 */
function t426() {
	return run_and_check("426\n","Parking cost = $15","Parking cost = $14");
}

/** @test
    @score  0
    @prereq t158 t194 t380 */
function t455() {
	return run_and_check("455\n","Parking cost = $15","Parking cost = $16");
}

/** @test
    @score  0
    @prereq t426 t455 */
function t639() {
	return run_and_check("639\n","Parking cost = $18","Parking cost = $14");
}

/** @test
    @score  0
    @prereq t426 t455 */
function t720() {
	return run_and_check("720\n","Parking cost = $19","Parking cost = $20");
}

/** @test
    @score  0
    @prereq t426 t455 */
function t730() {
	return run_and_check("730\n","Parking cost = $20","Parking cost = $21");
}

/** @test
    @score  0
    @prereq t426 t455 */
function t800() {
	return run_and_check("800\n","Parking cost = $21","Parking cost = $20");
}

/** @test
    @score  0
    @prereq t639 t720 t730 t800 */
function t1000() {
	return run_and_check("800\n","Parking cost = $21","Parking cost = $20");
}

/** @test
    @score  0
    @prereq t639 t720 t730 t800 */
function t1150() {
	return run_and_check("1150\n","Parking cost = $21","Parking cost = $20");
}

/** @test
    @prereq t1000 t1150
    @score  1.0 */
function points()
{
    return _count_case(true);
}