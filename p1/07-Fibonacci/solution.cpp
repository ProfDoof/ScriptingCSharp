#include <iostream>
using namespace std;

// Recursive function for calculating Fibonacci values
int fib(int n) {
	if (n <= 1)
		return n;
	else
		return fib(n-1)+fib(n-2);
}

int main()
{
	int f;
	cout << "This program calculates numbers in the Fibonacci sequence.\n";
	cout << "Which place in the sequence do you want to calculate? ";
	cin >> f;
	cout << endl;
	cout << "Fibonacci(" << f << ") is " << fib(f) << endl;
		
}

