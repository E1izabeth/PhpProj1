using System;
using System.CodeDom.Compiler;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;


namespace CgiRunner1
{
    class Program
    {
        static void Main(string[] args)
        {
            if (args.Length < 1)
            {
                Console.WriteLine("Usage: CgiRunner.exe <script file name>");
            }
            else if (!File.Exists(args[0]))
            {
                Console.WriteLine("File " + args[0] + " does not exists!");
            }
            else
            {
                DoWork(args);
            }
        }

        static void DoWork(string[] args)
        {
            try
            {
                var sourceFileName = args[0];
                var compiler = CreateCompiler(sourceFileName);

                var compilerParams = new CompilerParameters() {
                    GenerateInMemory = true,
                    GenerateExecutable = true,
                };

                var compilerResult = compiler.CompileAssemblyFromFile(compilerParams, sourceFileName);

                if (compilerResult.Errors.Count == 0)
                {
                    compilerResult.CompiledAssembly.EntryPoint.Invoke(null, new[] { args });
                }
                else
                {
                    Console.WriteLine("500 Internal Server Error");
                    Console.WriteLine();
                    compilerResult.Errors.OfType<CompilerError>().ToList().ForEach(Console.WriteLine);
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("500 Internal Server Error");
                Console.WriteLine();
                Console.WriteLine(ex.ToString());
            }
        }

        static CodeDomProvider CreateCompiler(string sourceFileName)
        {
            switch (Path.GetExtension(sourceFileName).ToLower())
            {
                case ".vb": return new Microsoft.VisualBasic.VBCodeProvider();
                case ".cs": return new Microsoft.CSharp.CSharpCodeProvider();
                default: throw new NotImplementedException();
            }
        }
    }
}
