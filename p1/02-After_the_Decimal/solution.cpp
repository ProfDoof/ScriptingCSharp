#include <iostream>
using namespace std;

int main()
{
	double n;
	cout << "Enter number: "; cin >> n;
	cout << endl;
	cout << "After the decimal: " << n - (int) n << endl;
}