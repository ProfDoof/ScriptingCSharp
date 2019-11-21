#include <iostream>
using namespace std;

int main()
{
    float f;
	cout << "Enter a number: ";
	cin >> f;
	
	int i;
	if( f >= 0 )
		i = (int) (f + 0.5);
    else
        i = (int) (f - 0.5);
	
	cout << "The rounded number is " << i << endl;

}
