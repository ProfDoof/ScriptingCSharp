#include <cstdlib>
#include <cstdio>
#include <ctime>
#include <iostream>
using namespace std;
#define assert(x) if (x) ; else (fprintf(stderr,"%s:%d: assert(%s) failed\n",__FILE__,__LINE__,#x),exit(1))
#if WIN32
    #define WIN32_LEAN_AND_MEAN 1
    #include <windows.h>
#endif
#include "Geometry.h"

#include "subtests.cpp"

int main(int argc,char *argv[])
{
#if WIN32
    DWORD dwMode = SetErrorMode(SEM_NOGPFAULTERRORBOX);
    SetErrorMode(dwMode | SEM_NOGPFAULTERRORBOX);
#endif

    if (argc != 2)
        fprintf(stderr,"must have exactly one argument (not %d)\n",argc);
    else {
        int i = atoi(argv[1]);
        if (i == 0)
            fprintf(stderr,"argument must be positive integer\n");
        else
            tests[i-1]();
    }
}
