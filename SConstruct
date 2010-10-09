# -*- coding: utf-8 -*-
# vim: set filetype=python

import glob
import os, os.path
from os.path import dirname, basename

fh = open('src/version', 'rt')
pkg_name = 'Erebot'
pkg_email = 'clicky@erebot.net'
pkg_version = fh.read().strip()
fh.close()
del fh

env = Environment()

def parse_bool(value):
    return isinstance(value, bool) and value or \
        value.lower() in ('true', '1', 'on', 'yes', 't', 'y')

def irglob(dname, pattern):
    it = glob.iglob('%s/%s' % (dname, pattern))
    while True:
        try:
            yield it.next()
        except StopIteration:
            break

    it = glob.iglob('%s/*/' % dname)
    for d in it:
        for f in irglob(d, pattern):
            yield f

component = 'Erebot'
sources = list(irglob('src/', '*.php'))
POT = 'i18n/%s.pot' % component
locales = glob.iglob('i18n/*/LC_MESSAGES')
POs = ["%s/%s.po" % (l, component) for l in locales
        if l.split('/')[1] != 'en_US']
MOs = ["%s/%s.mo" % (l, component) for l in locales]
english_PO = 'i18n/en_US/LC_MESSAGES/%s.po' % component

modules = [basename(dirname(m)) for m in glob.iglob('modules/*/SConstruct')]
deps = {'PLOP': 'src/logging/'}
for module in modules:
    deps[module] = "modules/%s/" % module

for tool in (
        'echo',
        'sed',
        'svn',
        'rm',
        'tar',
        'xgettext',
        'msgen',
        'msgfmt',
        'msginit',
        'msgmerge',
        'phpunit',
        'doxygen',
        'make',
        'scons',
    ):
    env[tool] = env.WhereIs(tool)

def init_catalog(target, source, env):
    destdir = Dir('i18n/%s/LC_MESSAGES/' % ARGUMENTS['catalog'])
    if not os.path.exists(str(destdir)):
        Execute(Mkdir(destdir))
    Execute(env.Action(
        '$msginit --no-translator -w 80 -l %(catalog)s '
        '-i %(source)s -o %(dest)s' % {
            'catalog': ARGUMENTS['catalog'],
            'dest': destdir.File('%s.po' % component),
            'source': source[0],
        }
    ))

def extract_messages(target, source, env, for_signature):
    return (
        '$xgettext -o $TARGET '
        '--from-code utf-8 --foreign-user --no-location '
        '-E -i -w 80 -s -L PHP --omit-header --strict --force-po '
        '--package-name %(name)s --package-version %(version)s '
        '--msgid-bugs-address %(email)s $SOURCES' % {
            'name': pkg_name,
            'version': pkg_version,
            'email': pkg_email,
        }
    )

def update_catalog(target, source, env, for_signature):
    return [
        '$msgmerge --backup=off -U -N -e -i --strict -w 80 '
        '-s -q --no-location $TARGET $SOURCE'
        for t in target
    ]

def translate_catalog(target, source, env, for_signature):
    return (
        '$msgen -e -i --strict --force-po -w 80 -s --no-location -o $TARGET $SOURCE'
    )

def compile_catalog(target, source, env, for_signature):
    return [
        '$msgfmt -f -o %(target)s %(source)s' % {
            'target': target[i],
            'source': source[i],
        }
        for i in xrange(len(source))
    ]

def add_tagfiles():
    return " ".join([
        '%(path)s%(module)s.tagfile='
                '../../%(path)sdoc/html' % {
            'path': path,
            'module': module,
        } for (module, path) in deps.iteritems()
    ])

env['BUILDERS']['ExtractMessages'] =    Builder(generator=extract_messages)
env['BUILDERS']['UpdateCatalog'] =      Builder(generator=update_catalog, suffix='.po', src_suffix='.pot', single_source=True)
env['BUILDERS']['TranslateCatalog'] =   Builder(generator=translate_catalog, suffix='.po', src_suffix='.pot', single_source=True)
env['BUILDERS']['CompileCatalog'] =     Builder(generator=compile_catalog, src_suffix='.po', suffix='.mo', single_source=True)


# Documentation
env.Requires('%s.tagfile' % component, 'doc')
env.Command('doc',
    ['Doxyfile'] + sources + ['%s/doc/' % dep for dep in deps.values()], [
        env.Action('$doxygen', chdir=1),
        env.Action('$make -C doc/latex', chdir=1),
    ], ENV=dict(
        EREBOT_VERSION=pkg_version,
        EREBOT_MODULE=component,
        EREBOT_TAGFILES=add_tagfiles(),
    )
)
env.SideEffect(['%s.tagfile' % component], 'doc')
env.Clean('doc', ['%s.tagfile' % component, 'doc'])

# Unit tests
env.Command('test', ['phpunit.xml'], '$phpunit',
    ENV=dict(
        PHP_GETTEXT_PATH=os.environ.get('PHP_GETTEXT_PATH', ''),
    )
)
env.AlwaysBuild('test')

# I18N
env.ExtractMessages(POT, sources)
env.Alias('extract_messages', POT)

env.UpdateCatalog(POs, POT)
env.TranslateCatalog(english_PO, POT)
env.Alias('update_catalog', POs + [english_PO])

i18n = env.CompileCatalog(source=POs + [english_PO])
env.Alias('compile_catalog', MOs)

if 'catalog' in ARGUMENTS:
    target =    "i18n/%s/LC_MESSAGES/%s.po" % (ARGUMENTS['catalog'], component)
    env.Command(target, POT, [
        Mkdir('i18n/%s/LC_MESSAGES/' % ARGUMENTS['catalog']),
        '$msginit --no-translator -w 80 -l %s -i $SOURCE -o $TARGET' % ARGUMENTS['catalog'],
    ])
    env.Alias('init_catalog', target)

env.NoClean(POT, english_PO, *POs)
env.Precious(POT, english_PO, *POs)

Default(i18n)

if 'version' in ARGUMENTS:
    commands = []
    if 'tag' in ARGUMENTS and parse_bool(ARGUMENTS['tag']):
        prefix = '../tags/Erebot-%s' % pkg_version
        commands.extend([
            "$scons",
            "$scons doc",
            "$svn cp --ignore-externals . %s" % prefix,
            # @FIXME:   Once we're done porting this to the new arch,
            #           we should remove 'user/' from the trunk too.
            "$rm -rf %(prefix)s/user/ "
                "%(prefix)s/.sconsign.dblite "
                "%(prefix)s/src/logging/.sconsign.dblite "
                "%(prefix)s/Erebot.xml "
                "%(prefix)s/deps/ "
                "%(prefix)s/conf.d/ "
                % {'prefix': prefix},
            "$svn ci -m 'Tag for version %s' %s" % (pkg_version, prefix),
            "$tar cjvf ../tags/Erebot-%s.tar.bz2 %s/ --xform 's@tags/@@' --exclude-vcs --show-transformed-names",
        ])
    commands.extend([
        """$sed -i 's/^\\(\\s\\+\\)version=.*/\\1version="%s"/' """
            "tests/data/valid-config.xml Erebot.xml.dist" % ARGUMENTS['version'],
        """$echo "%s" > src/version""" % ARGUMENTS['version'],
    ])
    if 'tag' in ARGUMENTS and parse_bool(ARGUMENTS['tag']):
        commands.append("$svn ci -m 'Bump version to %s' ." % ARGUMENTS['version'])
    env.Command('src/version', [], commands)
    env.Alias('bump', 'src/version')

# Export some variables to sub-scripts.
Export('pkg_name')
Export('pkg_version')
Export('pkg_email')
Export('env')
Export('init_catalog')
Export('irglob')
Export('ARGUMENTS')
Export('parse_bool')

# Include sub-scripts for PLOP & modules.
if ('recursive' not in ARGUMENTS) or \
    parse_bool(ARGUMENTS['recursive']):
    for module, path in deps.iteritems():
        SConscript("%s/SConstruct" % path, exports={
            'component': module,
        })

