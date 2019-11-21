#include <iostream>
using namespace std;

int main()
{
	int n;
	cout << "This program checks if the user inputs zero." << endl;
	cout << "Enter an integer: "; cin >> n; 
	
	if( n == 0 )
		cout << "Yes, zero was provided as input." << endl;
}

