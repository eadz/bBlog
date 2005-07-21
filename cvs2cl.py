import sys
import os, os.path
import re, string

asMacro = string.find(sys.executable, 'python') < 0
if asMacro:
  from cvsgui.App import *
  from cvsgui.Cvs import *
  from cvsgui.CvsEntry import *
  from cvsgui.Macro import *
  from cvsgui.MacroUtils import *

"""
  CvsGui Macro and stand-alone script "Build ChangeLog"
  $Revision: 1.1 $

  written by Oliver Giesen, Oct 2002 - Nov 2003
  contact:
    email:  ogware@gmx.net
    jabber: ogiesen@jabber.org
    icq:    18777742

  Feel free to modify or distribute in whichever way you like,
   as long as it doesn't limit my personal rights to
   modify and redistribute this code.
   Apart from that the code is supplied "as-is", i.e. without warranty of any
   kind, either expressed or implied, regarding its quality or security.
   Have fun!

  ATTENTION:
   You will need at least WinCvs 1.3.5 to execute any Python macros
   from within WinCvs! This macro has originally been written against
   WinCvs 1.3.8 .

  ======
  Usage (as CvsGui macro "Build ChangeLog"):

  - Select one or more CVS-folders and/or files

  - Run the macro from the Macros|CVS menu

   ~the ChangeLog file should get created

  ------   
  Usage (as CvsGui macro "Build ChangeLog (advanced)..."):

  - Select one or more CVS-folders and/or files

  - Run the macro from the Macros|CVS menu

  - Enter the filename of the logfile you want to generate/update
    (will be created inside the current directory - defaults to "ChangeLog")

  - Enter optional additional arguments to be passed to the log command

   ~the log file should get created

  ------
  Usage (as CvsGui admin macro "Build ChangeLog for module..."):

  - Run the macro from the Admin|Admin macros|CVS menu

  - Enter or confirm the CVSROOT of the repository to log
    (defaults to CVSROOT of current directory if applicable)

  - Enter or confirm the name of the module to log
    (defaults to module corresponding to current directory if aplicable)

  - Enter or confirm the file name of the log file you want to create/update
    (defaults to [module].log if applicable otherwise [cwd].log)

  - Enter optional additional arguments to be passed to the log command

   ~the log file should get created
  
  ======
  Usage (as stand-alone script):

    cvs2cl.py [[[basedir] filename] arg1] [arg2]...

  basedir	the directory for which the ChangeLog should be generated
  			(also where the logfile will be saved to)
  			optional - defaults to the current directory

  filename	the name of the logfile to be generated/updated
  			optional - defaults to "ChangeLog"

  arg1..n	optional additional arguments to be passed to the log command

  Example:
    cvs2cl.py d:\Dev\cvsgui MyChangeLog -w
      retrieves the log of my changes to the cvsgui project
       into d:\Dev\cvsgui\MyChangeLog

  ------
  or for modules-based ChangeLog:

    cvs2cl.py -r cvsroot module filename [arg1] [arg2]...

  cvsroot	the CVSROOT of the repository you want to log

  module	the name of the module you want to log

  filename	the name of the logfile to be generated/updated

  arg1..n	optional additional arguments to be passed to the rlog command  

  =============
  Known Issues / "Un-niceties":

  - The log-by-module currently displays the complete path of the RCS file
    which includes the path section from the CVSROOT. Truncating that part
    based on the known CVSROOT unfortunately doesn't always work, 
    e.g. Sourceforge appears to use symbolic links for publishing CVSROOTs
    while the actual path returned by cvs rlog is much longer.
    Have to think some more about this one...

  Please report any problems you may encounter or suggestions you might have
  to ogware@gmx.net .
    
"""

def runCvs(args):
  if asMacro:
    cvs = Cvs(1,0)
    return cvs.Run(*args)
  else:
    f_in, f_out, f_err = os.popen3('cvs '+string.join(args))
    out = f_out.read()
    err = f_err.read()
    f_out.close()
    f_err.close()
    code = f_in.close()
    if not code: code = 0
    return code, out, err

def wrapLine( line, wrapmargin=64):
  result = []
  while len(line) > wrapmargin:
    for i in range( wrapmargin, 1, -1):
      if not line[i] in string.letters+string.digits+'@_.':
        result.append( line[:i+1])
        line = line[i+1:]
        break
  result.append( line)
  return result


class ChangeLogger:
  def __init__(self, basedir = None, targets = [], logname = 'ChangeLog', \
               command = 'log', cvsroot = None):
    try:
      filename, oldlog, lastDate = self.prepareLogFile(basedir, logname)
      clName = filename[len(basedir)+1:]
      code, logdump = self.getLog(basedir, lastDate, targets, command, cvsroot)
      if code > 0:
        print logdump
      else:
        entries = ChangeLogParser(clName, command).parseLog(logdump)
        ChangeLogWriter().writeLog(entries, filename, oldlog)
        print 'Done.'
    except AssertionError, detail:
      print detail

  def prepareLogFile( self, basedir, logname):
    filename = os.path.join( basedir, logname)
    lastDate = None
    logfile = []
    print
    if os.path.exists( filename):
      print 'Analyzing existing %s...' % logname
      clFile = open( filename, 'r')
      try:
        oldlog = clFile.readlines()
        assert (len(oldlog)>0) and(len(oldlog[0])>=10), \
               'Existing %s has unknown format and could not be rewritten!' % logname
        lastDate = oldlog[0][:10]
        #print lastDate
        rx = re.compile( '^[0-9]{4}[\-\/][0-9]{2}[\-\/][0-9]{2}')
        assert rx.match(lastDate), 'Existing %s has unknown format and could not be rewritten!' % logname
        i = 0
        for line in oldlog[1:]:
          m = rx.match(line)
          if m and m.group()<>lastDate:
            break
          else:
            i+=1
        logfile += oldlog[i:]
        #print string.join( logfile[:25], '')
      finally:
        clFile.close()
    return filename, logfile, lastDate
        
  def getLog( self, basedir, lastDate, targets, command = 'log', cvsroot = None):
    print 'Downloading the log',
    if lastDate:
      print 'beginning %s ' % lastDate,
    print '...'
    cwdbup = os.getcwd()
    try:
      os.chdir( basedir)
      args = ['-Q', '-z9', command]
      if cvsroot:
        args.insert(0, '-d'+cvsroot)
      if lastDate:
        fstr = '-d%s<'
        if not asMacro:
          fstr = '"%s"'%fstr
        args += [fstr%lastDate]
      args += targets
      #print 'cvs', string.join( args), '\t(in %s)'%os.getcwd()
      code, out, err = runCvs(args)
    finally:
      os.chdir(cwdbup)
    if code == 0:
      return code, out
    else:
      return code, err
      

class ChangeLogParser:
  def __init__(self, clName, command = 'log'):
    self.clName = clName
    self.rlog = command == 'rlog'

  def parseRevision( self, text, filename, branchnames, entries):
    if text == '': return
    lines = text.splitlines()
    if len(lines) <= 2: return #skip totally empty revisions
    revno = re.match( '^revision ([0-9\.]+)', lines[0]).group(1)
    m = re.match( '^date: ([^ ]+).*author: ([^;]+)', lines[1])
    assert m, 'Unknown revision detail format! ("%s")'%lines[1]
    date = string.replace( m.group(1), '/', '-')
    author = m.group(2)
    #skip "branches" line, if any:
    txtidx = 2
    if lines[txtidx][:9] == 'branches:':
      txtidx+=1
    revtext = string.join( lines[txtidx:], '\n')
    if revtext == 'Initial revision' or revtext == 'no message':
      return
    
    del lines, txtidx
    if not entries.has_key(date):
      entries[date]={}
    if not entries[date].has_key(author):
      entries[date][author] = {}
    if not entries[date][author].has_key(revtext):
      entries[date][author][revtext] = []

    rootrev = re.search('(.*)\.[0-9]+$', revno).group(1)
    if branchnames.has_key(rootrev):
      branch = '[%s] '%branchnames[rootrev]
    else:
      branch = ''
    entries[date][author][revtext].append( '%s %s%s'%(filename, branch, revno))
    
  def parseFile( self, text, entries):
    #don't even scan files without selected revisions:
    m = re.search( 'selected revisions: ([0-9]+)', text, re.MULTILINE)
    if m and m.group(1) == '0':
      return
    #parse header:
    filename = None
    branchnames= {}
    section = 0
    idx = 0
    lines = text.splitlines()
    for line in lines:
      if section == 0:
        if not self.rlog:
          m = re.match( '^Working file: ([^,]+)', line)
          if m:
            filename = m.group(1)
            #don't log changes to ourselves:
            if filename == self.clName:
              return
            section+=1
        else:
          m = re.match( '^RCS file: [\/]?(([^\/,]+?\/)*([^\/,]+)),v', line)
          if m:
            #filename = m.group(m.lastindex)
            #if filename == self.clName:
            #  return
            filename = m.group(1)
            section+=1
      elif section == 1:
        if line[:15] == 'symbolic names:':
          section+=1
      elif section == 2:
        if line[0] == '\t':
          m = re.search( '^\t([^:]+): ([0-9\.]+)\.0(\.[0-9]+)', line)
          if m:
            branchnames[m.group(2)+m.group(3)] = m.group(1)
        else:
          section+=1
      else:
        if line == '-'*28:
          break
      idx+=1
    else:
      return

    del section
    #iterate revisions:
    revisions = string.split( string.join( lines[idx:], '\n'), '-'*28+'\n')
    del lines, idx
    for revision in revisions:
      self.parseRevision( revision, filename, branchnames, entries)
    
  def parseLog( self, logdump):
    print 'Parsing log output...'
    logentries = {}
    files = string.split( logdump, '='*77)
    #print '(%d files,'%len(files),
    for file in files:
      self.parseFile( file, logentries)
    #print '%d entries)'%len(logentries)
    return logentries

class ChangeLogWriter:
  def writeLogMsg( self, msg, file):
    indent = 2
    dowrap = (len(msg)>64) and( string.count( msg, '\n')== 0)
    for line in msg.splitlines():
      if dowrap:
        for wrappedline in wrapLine( line):
          file.write( '\t'*indent+wrappedline+'\n')
      else:
        file.write( '\t'*indent+line+'\n')
        
  def writeLog( self, entries, filename, oldlog):
    print 'Sorting entries...'
    dates = entries.keys()
    dates.sort()
    dates.reverse()
    print 'Rewriting %s...' % os.path.basename(filename)
    clFile = open( filename, 'w')
    try:
      pos = 0
      for date in dates:
        authors = entries[date]
        for author, logs in authors.items():
          clFile.write( '%s\t%s\n'%(date, author))
          for log, files in logs.items():
            files.sort()
            for file in files:
              clFile.write( '\t* %s:\n'%file)
            self.writeLogMsg( log, clFile)
            pos = clFile.tell()
            clFile.write('\n')
      clFile.seek(pos)
      clFile.writelines( oldlog)
    finally:
      clFile.close()

if asMacro:
  class BuildChangeLog( Macro):
    def __init__( self):
      Macro.__init__( self, 'Build ChangeLog', MACRO_SELECTION, 0, 'CVS')
      
    def OnCmdUI( self, cmdui):
      self.sel = App.GetSelection()
      enabled = len( self.sel) > 0
      if enabled:
        for entry in self.sel:
          if entry.IsUnknown():
            enabled = 0
            break
      cmdui.Enable( enabled)
    
    def Run( self):
      targets = []
      basedir = prepareTargets(self.sel, targets, 0)
      ChangeLogger(basedir, targets)


  class BuildChangeLogEx(BuildChangeLog):
    def __init__(self):
      Macro.__init__(self, 'Build ChangeLog (advanced)...', MACRO_SELECTION, 0, 'CVS')

    def Run(self):
      args = []
      basedir = prepareTargets(self.sel, args, 0)
      ok, filename = App.PromptEditMessage('Logfile name:\n'\
                                          +'(will be created in "%s")' % basedir,\
                                           'ChangeLog')
      if ok:
        ok, extargs = App.PromptEditMessage('Extended log options\n'\
                                           +'Examples:\n'\
                                           +'-w\tonly lists own changes'\
                                           +'-rBRANCH,FROMTAG:TOTAG\n\tlists changes between tags on branch'\
                                           +'-d2003-01-01<2003-12-31\n\tall changes of 2003',\
                                            '')
        if ok:
          ChangeLogger(basedir, string.split(extargs) + args, filename)


  class BuildModuleLog(Macro):
    def __init__(self):
      Macro.__init__(self, 'Build ChangeLog for module...', MACRO_ADMIN, 0, 'CVS')

    def readFile(self, file):
      if os.path.exists(file):
        f = open(file, 'r')
        try:
          line = string.strip(f.readline())
          return line
        finally:
          f.close()
      else:
        return ''
      
    def Run(self):
      basedir = os.getcwd()
      croot = self.readFile(os.path.join(basedir, 'CVS', 'Root'))
      ok, cvsroot = App.PromptEditMessage('CVSROOT', croot)
      if ok:
        if croot == cvsroot:
          module = self.readFile(os.path.join(basedir, 'CVS', 'Repository'))
        else:
          module = os.path.basename(basedir)
        ok, module = App.PromptEditMessage('Module to log:', module)
        if ok:
          ok, filename = App.PromptEditMessage('Logfile name:\n'\
                                              +'(will be created in "%s")' % basedir,\
                                               os.path.basename(module)+'.log')
          if ok:
            ok, extargs = App.PromptEditMessage('Extended log options\n'\
                                               +'Examples:\n'\
                                               +'-w\tonly lists own changes'\
                                               +'-rBRANCH,FROMTAG:TOTAG\n\tlists changes between tags on branch'\
                                               +'-d2003-01-01<2003-12-31\n\tall changes of 2003',\
                                                '')
            if ok:
              args = [module]
              ChangeLogger(basedir, string.split(extargs+' ') + args, filename, \
                           'rlog', cvsroot)

          
  BuildChangeLog()
  BuildChangeLogEx()
  BuildModuleLog()
else:
  filename = 'ChangeLog'
  args = []
  argc = len(sys.argv)
  if argc > 1:
    if sys.argv[1] == '-r':
      basedir = os.getcwd()
      assert argc >= 5, 'Must specify CVSROOT, module and filename in remote mode!'
      cvsroot = sys.argv[2]
      filename = sys.argv[4]
      if argc > 5:
        args += sys.argv[5:]
      args += [sys.argv[3]]
    else:
      basedir = sys.argv[1]
      if argc > 2:
        filename = sys.argv[2]
        if argc > 3:
          args += sys.argv[3:]
  else:
    basedir = os.getcwd()
    cvsroot = None

  if cvsroot:
    ChangeLogger(basedir, args, filename, 'rlog', cvsroot)
  else:
    ChangeLogger(basedir, args, filename)
