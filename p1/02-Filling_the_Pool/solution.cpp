#include <iostream>
using namespace std;

int main()
{
	int length, width, depth, rate;
	double time;
	
	cout << "Enter pool dimensions\n";
	cout << "Length: "; cin >> length;
	cout << "Width: "; cin >> width;
	cout << "Depth: "; cin >> depth;
	cout << endl;
	cout << "Water entry rate: "; cin >> rate;
	cout << endl;
	
	time = length*width*depth*7.48/rate;
	
	cout << "The pool will fill completely in " << time << " minutes." << endl;
}
